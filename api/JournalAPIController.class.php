<?php

class JournalAPIController extends BaseAPIController {

	private $journal;
	private $sqlQuery;
	private $documentTypeFactory;

	public function __construct(
		Journal $journal,
		SQLQuery $sqlQuery,
		DocumentTypeFactory $documentTypeFactory
	){
		$this->journal = $journal;
		$this->sqlQuery = $sqlQuery;
		$this->documentTypeFactory = $documentTypeFactory;
	}

	/**
	 * @api {get} /Journal/list /Journal/list
	 * @apiName /Journal/list
	 * @apiDescription Récupérer le journal (was: /journal.php)
	 * @apiGroup Journal
	 * @apiVersion 2.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {string} recherche Champs de recherche sur le contenu du message horodaté
	 * @apiParam {string} id_user Identifiant de l'utilisateur
	 * @apiParam {string} date_debut Date à partir de laquelle les informations sont récupérées.
	 * @apiParam {string} date_fin Date au delà de laquelle les informations ne sont plus récupérées.
	 * @apiParam {string} id_d Identifiant du document
	 * @apiParam {string} type Type de document (retourné par document-type.php)
	 * @apiParam {string} format Format du journal : json(par défaut) ou bien csv
	 * @apiParam {int} offset Numéro de la première ligne à retourner (0 par défaut)
	 * @apiParam {int} limit Nombre maximum de lignes à retourner (100 par défaut)
	 * @apiParam {int} csv_entete_colonne indique si on doit afficher l'entete des colonne (false par défaut)
	 *
	 * @apiSuccess {objet[]} journal
	 * @apiSuccess {int} id_j Numéro unique, auto-incrémentiel et sans trou du journal
	 * @apiSuccess {string} type
	 * 					1. Action sur un document
	 *					2 : Notification
	 * 					3 : Modification d'une entité
	 * 					4 : Modification d'un utilisateur
	 * 					5 : Mail sécurisé
	 * 					6 : Connexion
	 * 					7 : Consultation d'un document"
	 * @apiSuccess {int} id_e Identifiant de l'entité
	 * @apiSuccess {int} id_u Identifiant de l'utilisateur
	 * @apiSuccess {sting} id_d Identifiant du document
	 * @apiSuccess {string} action Action effectuée
	 * @apiSuccess {string} message Message
	 * @apiSuccess {string} date Date de l'ajout dans le journal (peut-être différents de l'horodatage)
	 * @apiSuccess {string} date_horodatage Date récupéré dans le jeton d'horodatage.
	 * @apiSuccess {string} message_horodate Message qui a été horodaté
	 * @apiSuccess {string} titre Titre du document
	 * @apiSuccess {string} document_type Type du document
	 * @apiSuccess {string} denomination Nom de l'entité
	 * @apiSuccess {string} nom Nom de l'utilisateur
	 * @apiSuccess {string} prenom Prénom de l'utilisateur
	 *
	 */
	public function listAction(){
		$offset = $this->getFromRequest('offset',0);
		$limit = $this->getFromRequest('limit',100);
		$id_e = $this->getFromRequest('id_e',0);
		$type = $this->getFromRequest('type');
		$id_d = $this->getFromRequest('id_d');
		$id_user = $this->getFromRequest('id_user');
		$recherche = $this->getFromRequest('recherche');
		$date_debut = $this->getFromRequest('date_debut');
		$date_fin = $this->getFromRequest('date_fin');
		$format = $this->getFromRequest('format');
		$csv_entete_colonne = $this->getFromRequest('csv_entete_colonne', 0);

		$id_u = $this->getUtilisateurId();

		if   (! $this->getRoleUtilisateur()->hasDroit($id_u,"journal:lecture",$id_e)){
			throw new Exception("Acces interdit id_e=$id_e, id_d=$id_d,id_u=$id_u,type=$type");
		}


		if ($format != 'csv'){
			$result = $this->journal->getAll($id_e, $type, $id_d, $id_user, $offset, $limit, $recherche, $date_debut, $date_fin,false,false);
			return $result;
		}

		// Pour éviter des problèmes mémoires, au format CSV :
		//  - Utilisation de Pdo. La lecture du recordset se fait ligne à ligne. Pas de chargement de la totalité du recordset en mémoire.
		//  - comme le parcours des lignes peut être long, réinitialisation du temps max_execution_time la chaque boucle.
		//  - Génération du fichier csv dans le répertoire /tmp puis retourné
		// NB : Le problème "mémoire", existe toujours pour le format JSON.


		$filecsv = tempnam('/tmp/', 'exportjournal');
		$handle = fopen($filecsv, 'w');

		$max_execution_time= ini_get('max_execution_time');

		$pdo = $this->sqlQuery->getPdo();
		list($sql, $param_sql) = $this->journal->getQueryAll($id_e, $type, $id_d, $id_user, $offset, $limit, $recherche, $date_debut, $date_fin, true);
		$stmt = $pdo->prepare($sql);
		$stmt->execute($param_sql);

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			ini_set('max_execution_time', $max_execution_time);
			if ($csv_entete_colonne) {
				// Les entêtes sont les clés du tableau associatif
				$entetes = array_keys($row);
				// Suppression de la colonne preuve
				$index_col_preuve = array_search('preuve', $entetes, true);
				array_splice($entetes, $index_col_preuve, 1);
				// Compatibilité avec l'existant et journal->getAll() : ajout de 2 colonnes supplémentaires
				$entetes[] = 'document_type_libelle';
				$entetes[] = 'action_libelle';
				fputcsv($handle, $entetes);
				$csv_entete_colonne=false;
			}
			$row['message'] = preg_replace("/(\r\n|\n|\r)/", " ", $row['message']);
			$row['message_horodate'] = preg_replace("/(\r\n|\n|\r)/", " ", $row['message_horodate']);
			unset($row['preuve']);
			$documentType = $this->documentTypeFactory->getFluxDocumentType($row['document_type']);
			// Compatibilité avec l'existant et journal->getAll() : ajout de 2 colonnes supplémentaires
			$row['document_type_libelle'] = $documentType->getName();
			$row['action_libelle'] = $documentType->getAction()->getActionName($row['action']);
			fputcsv($handle, $row);
		}

		fclose($handle);
		//Export du fichier
		header_wrapper("Content-type: text/csv; charset=utf-8");
		header_wrapper("Content-disposition: attachment; filename=pastell-export-journal-$id_e-$type-$id_d.csv");
		readfile($filecsv);
		// Suppression du fichier temporaire après l'export
		unlink($filecsv);

		exit_wrapper(0);

		//Never reached...
		// @codeCoverageIgnoreStart
		return true;
		// @codeCoverageIgnoreEnd
	}


	/**
	 * @api {get} /Journal/preuve /Journal/preuve
	 * @apiDescription Récupère un élement de preuve du journal
	 * @apiGroup Journal
	 * @apiVersion 2.0.0
	 * @apiParam {int} id_j l'identifiant de la ligne du journal
	 * @apiSuccess {data} fichier_tsr le contenu du fichiet au format timestamp reply et renvoyé directement.	
	 */
	public function preuveAction(){
		$id_j = $this->getFromRequest('id_j');

		$info = $this->journal->getAllInfo($id_j);
		if (! $info){
			throw new Exception("Accès interdit {id_j=$id_j}");
		}
		$this->checkDroit($info['id_e'],'journal:lecture');

		header_wrapper("Content-type: application/timestamp-reply");
		header_wrapper("Content-disposition: attachment; filename=pastell-journal-preuve-$id_j.tsr");

		echo $info['preuve'];

		exit_wrapper(0);

		//Never reached...
		// @codeCoverageIgnoreStart
		return true;
		// @codeCoverageIgnoreEnd
	}


}