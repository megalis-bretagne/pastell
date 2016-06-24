<?php


class EntiteController extends BaseAPIController {

	//FIXME inverser cette dépendance...
	private $entiteControler;

	private $entiteSQL;

	public function __construct(
		EntiteControler $entiteControler,
		EntiteSQL $entiteSQL
	) {
		$this->entiteControler  = $entiteControler;
		$this->entiteSQL = $entiteSQL;
	}


	/**
	 * @api {get} /list-entite.php /Entite/list
	 * @apiDescription Liste l'ensemble des entités sur lesquelles l'utilisateur a des droits.
	 * 					Liste également les entités filles.
	 * @apiGroup Entite
	 * @apiVersion 1.0.0
	 * @apiSuccess {Object[]} entite Liste d'entité
	 * @apiSuccess {int} id_e Identifiant numérique de l'entité
	 * @apiSuccess {string} denomination Libellé de l'entité (ex : Saint-Amand-les-Eaux)
	 * @apiSuccess {string} siren Numéro SIREN de l'entité (si c'est une collectivité ou un centre de gestion)
	 * @apiSuccess {int} centre_de_gestion Identifiant numérique (id_e) du CDG de la collectivité
	 * @apiSuccess {int} entite_mere Identifiant numérique (id_e) de l'entité mère de l'entité (par exemple pour un service)
	 */
	public function listAction(){
		return $this->getRoleUtilisateur()->getAllEntiteWithFille($this->getUtilisateurId(),'entite:lecture');
	}

	/**
	 * @api {get} /create-entite.php /Entite/create
	 * @apiDescription Créer une entité
	 * @apiGroup Entite
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {string} denomination Libellé de l'entité (ex : Saint-Amand-les-Eaux)
	 * @apiParam {string} type Le type de l'entité
	 * @apiParam {string} siren Numéro SIREN de l'entité (si c'est une collectivité ou un centre de gestion)
	 * @apiParam {int} entite_mere Identifiant numérique (id_e) de l'entité mère de l'entité (par exemple pour un service)
	 *
	 * @apiSuccess {int} id_e Identifiant numérique de l'entité
	 */
	public function createAction() {
		$data = $this->getRequest();
		return $this->createEntite($data);
	}

	private function createEntite($data){

		// Si l'entité mère n'est pas renseignée, on se positionne sur l'identité racine (id_e=0)
		$entite_mere = isset($data['entite_mere']) ? $data['entite_mere'] : 0;

		// Vérification des droits.
		$this->verifDroit($entite_mere, "entite:edition");

		$type = $data['type'];
		$siren = $data['siren'];
		$denomination = $data['denomination'];
		$centre_de_gestion = isset($data['centre_de_gestion']) ? $data['centre_de_gestion'] : 0;

		$id_e_cree = $this->entiteControler->edition(null, $denomination, $siren, $type, $entite_mere, $centre_de_gestion, 'non', 'non');

		$info['id_e']= $id_e_cree;
		return $info;
	}

	/**
	 * @api {get} /create-entite.php /Entite/create
	 * @apiDescription Permet la suppression d'une entité soit par son identifiant, soit par sa dénomination.
	 * 					Dans le cas où les deux paramètres sont renseignés, seul l'identifiant sera pris en compte.
	 * 					Si deux entités portent le même nom, aucune action ne sera effectuée.
	 * @apiGroup Entite
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {string} denomination Libellé de l'entité (ex : Saint-Amand-les-Eaux)
	 * @apiParam {int} id_e Identifiant numérique de l'entité
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function deleteAction() {
		$data = $this->getRequest();

		// Chargement de l'entité depuis la base de données
		$entiteSQL = $this->entiteSQL;
		$infoEntiteExistante = $this->getEntiteFromData($data);
		$id_e = $infoEntiteExistante['id_e'];
		// Vérification des droits
		$this->verifDroit($id_e, "entite:edition");

		$entiteSQL->removeEntite($id_e);

		$result['result'] = self::RESULT_OK;
		return $result;
	}

	//TODO Documentation ?
	public function detailAction() {
		$id_e  = $this->getFromRequest('id_e');
		// Chargement de l'entité depuis la base de données
		$entiteSQL = $this->entiteSQL;
		$infoEntite = $entiteSQL->getInfo($id_e);

		if (!$infoEntite) {
			throw new Exception("L'entité n'existe pas : {id_e=$id_e}");
		}

		// Vérification des droits.
		$this->verifDroit($id_e, "entite:lecture");

		// Chargement des entités filles
		$resultFille = array();
		$entiteFille = $entiteSQL->getFille($id_e);
		if ($entiteFille) {
			//GDON : completer les TU pour passer dans la boucle.
			foreach($entiteFille as $key => $valeur) {
				$resultFille[$key] = array('id_e' => $valeur['id_e']);
			}
		}

		// Construction du tableau resultat
		$result=array();
		$result['id_e'] = $infoEntite['id_e'];
		$result['denomination'] = $infoEntite['denomination'];
		$result['siren'] = $infoEntite['siren'];
		$result['type'] = $infoEntite['type'];
		$result['entite_mere'] = $infoEntite['entite_mere'];
		$result['entite_fille'] = $resultFille;
		$result['centre_de_gestion'] = $infoEntite['centre_de_gestion'];

		return $result;
	}

	/**
	 * @api {get} /modif-entite.php /Entite/edit
	 * @apiDescription Créer une entité. Permet la modification d'une entité soit par son identifiant, soit par sa dénomination.
	 * 					Dans le cas où les deux paramètres sont renseignés, seul l'identifiant sera pris en compte.
	 * 					Si deux entités portent le même nom, aucune action ne sera effectuée.

	 * @apiGroup Entite
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {string} denomination Libellé de l'entité (ex : Saint-Amand-les-Eaux)
	 * @apiParam {string} type Le type de l'entité
	 * @apiParam {string} siren Numéro SIREN de l'entité (si c'est une collectivité ou un centre de gestion)
	 * @apiParam {int} entite_mere Identifiant numérique (id_e) de l'entité mère de l'entité (par exemple pour un service)
	 * @apiParam {int} centre_de_gestion Identifiant numérique (id_e) du centre de gestion
	 * @apiParam {boolean} create Flag permettant la création de l\'entité si aucune autre entité ne porte le même nom.(défaut FALSE)
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function editAction() {
		$data = $this->getRequest();

		$entite = $this->entiteSQL;

		// Possibilité de créer une entité si celle ci n'existe pas
		$createEntite = isset($data['create']) ? $data['create'] : FALSE;

		//Recherche de l'entite par son identifiant
		if(isset($data['id_e'])) {
			$id_e = $data['id_e'];
			$infoEntiteExistante = $entite->getInfo($id_e);
			if (!$infoEntiteExistante) {
				throw new Exception("L'identifiant de l'entite n'existe pas : {id_e=$id_e}");
			}
		}
		// Recherche de l'entité par sa dénomination
		elseif(isset($data['denomination'])) {
			$denomination = $data['denomination'];
			$numberOfEntite = $entite->getNumberOfEntiteWithName($denomination);

			//Si pas d'entité avec ce nom et que l'on n'a pas choisi de la créer
			if($numberOfEntite == 0 && !$createEntite) {
				throw new Exception("La dénomination de l'entité n'existe pas : {denomination=$denomination}");
			}
			elseif($numberOfEntite > 1) {
				throw new Exception("Plusieurs entités portent le même nom, préférez utiliser son identifiant");
			}

			$infoEntiteExistante = $entite->getInfoByDenomination($denomination);
		}
		// Impossible de rechercher l'entité sans son identifiant ni sa dénomination
		else {
			throw new Exception("Aucun paramètre permettant la recherche de l'entité n'a été renseigné");
		}

		// Si l'entite n'existe pas et qu'on a spécifié vouloir la créer
		if(!$infoEntiteExistante && $createEntite) {
			return $this->createEntite($data);
		}

		$id_e = $infoEntiteExistante['id_e'];
		// Sauvegarde des valeurs. Si elles ne sont pas présentes dans $data, il faut les conserver.
		$entite_mere = $infoEntiteExistante['entite_mere'];
		$centre_de_gestion = $infoEntiteExistante['centre_de_gestion'];

		// Vérification des droits sur l'entité
		$this->verifDroit($id_e, "entite:edition");
		// Vérification des droits sur l'entité mère
		if (array_key_exists("entite_mere", $data) && $data['entite_mere']) {
			$this->verifDroit($data['entite_mere'], "entite:edition");
		}

		// Modification de l'entité chargée avec les infos passées par l'API
		foreach($data as $key => $newValeur){
			if (array_key_exists($key, $infoEntiteExistante)) {
				$infoEntiteExistante[$key] = $newValeur;
			}
		}

		$type = $infoEntiteExistante['type'];
		$siren = $infoEntiteExistante['siren'];
		$denomination = $infoEntiteExistante['denomination'];
		if ($infoEntiteExistante['entite_mere']) {
			$entite_mere = $infoEntiteExistante['entite_mere'];
		}
		if ($infoEntiteExistante['centre_de_gestion']) {
			$centre_de_gestion = $infoEntiteExistante['centre_de_gestion'];
		}

		$this->entiteControler->edition($id_e, $denomination, $siren, $type, $entite_mere, $centre_de_gestion, 'non', 'non');

		$result['result'] = self::RESULT_OK;
		return $result;

	}

	//FIXME code duplication depuis UtilisateurController...
	private function getEntiteFromData(&$data) {
		$entite = $this->entiteSQL;
		//Recherche de l'entite par son identifiant
		if(isset($data['id_e'])) {
			$id_e = $data['id_e'];
			$infoEntiteExistante = $entite->getInfo($id_e);
			if (!$infoEntiteExistante) {
				throw new Exception("L'identifiant de l'entite n'existe pas : {id_e=$id_e}");
			}
		}
		// Recherche de l'entité par sa dénomination
		elseif(isset($data['denomination'])) {
			$denomination = $data['denomination'];
			$numberOfEntite = $entite->getNumberOfEntiteWithName($denomination);

			if($numberOfEntite == 0) {
				throw new Exception("La dénomination de l'entité n'existe pas : {denomination=$denomination}");
			}
			elseif($numberOfEntite > 1) {
				throw new Exception("Plusieurs entités portent le même nom, préférez utiliser son identifiant");
			}
			//Si une seule entité porte ce nom
			else {
				$infoEntiteExistante = $entite->getInfoByDenomination($denomination);
			}
		}
		// Impossible de rechercher l'entité sans son identifiant ni sa dénomination
		else {
			throw new Exception("Aucun paramètre permettant la recherche de l'entité n'a été renseigné");
		}

		return $infoEntiteExistante;
	}


}
