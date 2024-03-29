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

	public function get(){
		$id_j = $this->getFromQueryArgs(0);
		if ($id_j){
			return $this->detail();
		}

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

		$this->checkDroit($id_e,"journal:lecture");

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

	public function detail(){
		$preuve = $this->getFromQueryArgs(1);
		if ($preuve=='jeton'){
			return $this->getPreuve();
		} else if($preuve){
			throw new NotFoundException("Ressource $preuve non trouvée");
		}
		$id_j = $this->getFromQueryArgs(0);
		$info = $this->getInfo($id_j);
		$info['preuve'] = base64_encode($info['preuve']);
		return $info;
	}

	public function getPreuve(){
		$id_j = $this->getFromQueryArgs(0);
		$info = $this->getInfo($id_j);

		header_wrapper("Content-type: application/timestamp-reply");
		header_wrapper("Content-disposition: attachment; filename=pastell-journal-preuve-$id_j.tsr");

		echo $info['preuve'];

		exit_wrapper(0);

		//Never reached...
		// @codeCoverageIgnoreStart
		return true;
		// @codeCoverageIgnoreEnd
	}

	private function getInfo($id_j){
		$info = $this->journal->getAllInfo($id_j);
		if (! $info){
			throw new NotFoundException("L'événement $id_j n'a pas été trouvé");
		}
		$this->checkDroit($info['id_e'],"journal:lecture");
		return $info;
	}


}