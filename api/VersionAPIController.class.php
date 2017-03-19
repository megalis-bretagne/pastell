<?php

class VersionAPIController extends BaseAPIController {

	private $manifestFactory;

	public function __construct(ManifestFactory $manifestFactory){
		$this->manifestFactory = $manifestFactory;
	}


	/**
	 * @api {get} /version /version
	 * @apiDescription Information sur la version (anciennement version.php)
	 * @apiName /version
	 * @apiGroup Version
	 * @apiVersion 2.0.0
	 * @apiSuccess {string} version Numéro de version
	 * @apiSuccess {string} revision Numéro de révision SVN
	 * @apiSuccess {string} version_complete Concaténation de version et révision
	 * @apiSuccess {string} last_changed_date Date du dernier commit
	 * @apiSuccess {array} extensions_versions_accepted Liste des versions compatibles pour les extensions
	 *
	 *
	 * @apiSuccessExample {json} Success-Reponse:
	 * 		{
	 * 			"version":"2.0.0",
	 *	 		"revision":"1791",
	 * 			"last_changed_date":"$LastChangedDate: 2016-06-20 20:54:03 +0200 (Mon, 20 Jun 2016) $",
	 * 			"extensions_versions_accepted":["2.0.0"],
	 *	 		"version_complete":"Version 2.0.0 - R\u00e9vision  1791"
	 * 		}
	 */
	public function get(){
		$info = $this->manifestFactory->getPastellManifest()->getInfo();
		$result = array();
		$result['version'] = $info['version'];
		$result['revision'] = $info['revision'];
		$result['last_changed_date'] = $info['last_changed_date'];
		$result['extensions_versions_accepted'] = $info['extensions_versions_accepted'];
		$result['version_complete'] = $info['version-complete'];
		return $result;
	}

	public function post(){
		throw new MethodNotAllowedException("La méthode POST n'est pas disponible pour l'objet version");
	}

	//Pour le logiciel ALLO. Cette fonction ne fait pas partie de l'API publique.
 	public function alloAction(){
		$info = $this->manifestFactory->getPastellManifest()->getInfo();
		return array("produit"=>"Pastell","version"=>$info['version']);
	}
}