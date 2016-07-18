<?php


class EntiteAPIController extends BaseAPIController {
	
	private $entiteSQL;

	private $siren;

	private $entiteCreator;


	public function __construct(
		EntiteSQL $entiteSQL,
		Siren $siren,
		EntiteCreator $entiteCreator
	) {
		$this->entiteSQL = $entiteSQL;
		$this->siren = $siren;
		$this->entiteCreator = $entiteCreator;
	}


	/**
	 * @api {get} /Entite/list  /list-entite.php
	 * @apiDescription Liste l'ensemble des entités sur lesquelles l'utilisateur a des droits.
	 * 					Liste également les entités filles. (was:  /list-entite.php)
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
	 * @api {get} /Entite/create /create-entite.php
	 * @apiDescription Créer une entité (was:  /create-entite.php)
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
		$entite_mere = $this->getFromRequest('entite_mere',0);
		$type = $this->getFromRequest('type');
		$siren = $this->getFromRequest('siren');
		$denomination = $this->getFromRequest('denomination');
		$centre_de_gestion = $this->getFromRequest('centre_de_gestion',0);
		$info['id_e'] = $this->edition(null, $denomination, $siren, $type, $entite_mere, $centre_de_gestion);
		return $info;
	}

	/**
	 * @api {get} /Entite/delete /Entite/delete
	 * @apiDescription Permet la suppression d'une entité soit par son identifiant, soit par sa dénomination.
	 * 					Dans le cas où les deux paramètres sont renseignés, seul l'identifiant sera pris en compte.
	 * 					Si deux entités portent le même nom, aucune action ne sera effectuée.
	 * 					(was : /delete-entite.php)
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
		$infoEntiteExistante = $this->entiteSQL->getEntiteFromData($data);
		$id_e = $infoEntiteExistante['id_e'];

		$this->verifDroit($id_e, "entite:edition");

		$this->entiteSQL->removeEntite($id_e);

		$result['result'] = self::RESULT_OK;
		return $result;
	}

	/**
	 * @api {get}   /Entite/detail /Entite/detail
	 * @apiDescription Détail sur une entité (was: /detail-entite.php)
	 * @apiGroup Entite
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant numérique de l'entité
	 *
	 * @apiSuccess {string} denomination Libellé de l'entité (ex : Saint-Amand-les-Eaux)
	 * @apiSuccess {string} type Le type de l'entité
	 * @apiSuccess {string} siren Numéro SIREN de l'entité (si c'est une collectivité ou un centre de gestion)
	 * @apiSuccess {int} entite_mere Identifiant numérique (id_e) de l'entité mère de l'entité (par exemple pour un service)
	 * @apiSuccess {Object[]} entite_fille Tableau des entité filles (on ne ramène que l'id_e)
	 * @apiSuccess {int} id_e Identifiant numérique de l'entité
	 *
	 */
	public function detailAction() {
		$id_e  = $this->getFromRequest('id_e');
		$this->verifDroit($id_e, "entite:lecture");

		$infoEntite = $this->entiteSQL->getInfo($id_e);
		
		// Chargement des entités filles
		$resultFille = array();
		$entiteFille = $this->entiteSQL->getFille($id_e);
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
	 * @api {get} /Entite/edit /Entite/edit
	 * @apiDescription Créer une entité. Permet la modification d'une entité soit par son identifiant, soit par sa dénomination.
	 * 					Dans le cas où les deux paramètres sont renseignés, seul l'identifiant sera pris en compte.
	 * 					Si deux entités portent le même nom, aucune action ne sera effectuée.
	 * 					(was: /modif-entite.php)

	 * @apiGroup Entite
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {string} denomination Libellé de l'entité (ex : Saint-Amand-les-Eaux)
	 * @apiParam {string} type Le type de l'entité
	 * @apiParam {string} siren Numéro SIREN de l'entité (si c'est une collectivité ou un centre de gestion)
	 * @apiParam {int} entite_mere Identifiant numérique (id_e) de l'entité mère de l'entité (par exemple pour un service)
	 * @apiParam {int} centre_de_gestion Identifiant numérique (id_e) du centre de gestion
	 * @apiParam {boolean} create Flag permettant la création de l'entité si aucune autre entité ne porte le même nom.(défaut FALSE)
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function editAction() {

		$createEntite = $this->getFromRequest('create');

		if ($createEntite){
			return $this->createAction();
		}

		$data = $this->getRequest();

		$infoEntiteExistante = $this->entiteSQL->getEntiteFromData($data);

		$id_e = $infoEntiteExistante['id_e'];


		// Sauvegarde des valeurs. Si elles ne sont pas présentes dans $data, il faut les conserver.
		$entite_mere = $infoEntiteExistante['entite_mere'];
		$centre_de_gestion = $infoEntiteExistante['centre_de_gestion'];

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

		$this->edition($id_e, $denomination, $siren, $type, $entite_mere, $centre_de_gestion);

		$result['result'] = self::RESULT_OK;
		return $result;
	}

	private function edition($id_e,$nom,$siren,$type,$entite_mere,$centre_de_gestion){
		$this->verifDroit($entite_mere, "entite:edition");

		if ($id_e){
			$this->verifDroit($id_e, "entite:edition");
		}

		if (!$nom){
			throw new Exception("Le nom est obligatoire");
		}
		if (! in_array($type,array(Entite::TYPE_SERVICE,Entite::TYPE_CENTRE_DE_GESTION,Entite::TYPE_COLLECTIVITE))){
			throw new Exception("Le type d'entité doit être renseigné. Les valeurs possibles sont collectivite, service ou centre_de_gestion.");
		}

		if ($type == Entite::TYPE_SERVICE && ! $entite_mere){
			throw new Exception("Un service doit être ataché à une entité mère (collectivité, centre de gestion ou service)");
		}

		if ($type != Entite::TYPE_SERVICE) {
			if ( ! $siren ){
				throw new Exception("Le siren est obligatoire");
			}
			if (  ! $this->siren->isValid($siren)){
				throw new Exception("Le siren « $siren » ne semble pas valide");
			}
		}

		$id_e = $this->entiteCreator->edit($id_e,$siren,$nom,$type,$entite_mere,$centre_de_gestion);
		return $id_e;
	}

}
