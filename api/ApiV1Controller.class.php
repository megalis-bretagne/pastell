<?php

class ApiV1Controller {

	private $objectInstancier;
	/** @var JSONoutput */
	private $jsonOutput;

	public function __construct(ObjectInstancier $objectInstancier) {
		$this->objectInstancier = $objectInstancier;
		$this->jsonOutput = $objectInstancier->getInstance('JSONoutput');
	}

	public function go($api_function){

		$info = $this->getAPINameFromLegacyScript($api_function);

		$api_function = array_shift($info);
		$request_method = array_shift($info);

		$this->callJson($api_function,$info,$request_method);
	}

	public function callJson($controller,$action,$request_method){
		$result = array();
		try {
			$result = $this->callMethod($controller, $action,$request_method);
		} catch(Exception $e) {
			$result['status'] = 'error';
			$result['error-message'] = $e->getMessage();
		}
		$this->jsonOutput->sendJson($result,false);
	}

	public function callMethod($controller,$action,$request_method){
		/** @var BaseAPIControllerFactory $baseAPIControllerFactory */
		$baseAPIControllerFactory = $this->objectInstancier->getInstance('BaseAPIControllerFactory');
		return $baseAPIControllerFactory->callMethod($controller,$action,$request_method);
	}

	public function getFromRequest($key,$default = false){
		if (empty($_REQUEST[$key])){
			return $default;
		}
		return $_REQUEST[$key];
	}

	private function getAPINameFromLegacyScript($old_script_name){
		$legacy_script = array(
			'version.php' => array('version','get'),
			'list-roles.php' => array('role','get'),
			'document-type.php' => array('flux','get'),
			'document-type-info.php' => array('flux','get',$this->getFromRequest('type')),

			//TODO
			'document-type-action.php' =>  array('DocumenType','get','action'),

			'action.php' =>  array('document','get','action'),
			'action-connecteur-entite.php' =>  array('connecteur','get','doAction'),
			'add-role-utilisateur.php' =>  array('UtilisateurRole','get','add'),
			'add-several-role-utilisateur.php' =>  array('UtilisateurRole','get','add'),
			'create-connecteur-entite.php' =>  array('Connecteur','get','create'),
			'create-document.php' =>  array('document','get','create'),
			'create-entite.php' =>  array('entite','get','create'),
			'create-flux-connecteur.php' =>  array('connecteur','get','associateFlux'),
			'create-utilisateur.php' =>  array('Utilisateur','get','create'),
			'delete-connecteur-entite.php' =>  array('Connecteur','get','delete'),
			'delete-entite.php' =>  array('Entite','get','delete'),
			'delete-extension.php' =>  array('Extension','get','delete'),
			'delete-flux-connecteur.php' =>  array('Connecteur','get','deleteFluxConnecteur'),
			'delete-role-utilisateur.php'=>  array('UtilisateurRole','get','delete'),
			'delete-several-roles-utilisateur.php' =>  array('UtilisateurRole','get','deleteSeveral'),
			'delete-utilisateur.php' =>  array('Utilisateur','get','delete'),
			'detail-connecteur-entite.php' =>  array('Connecteur','get','detail'),
			'detail-document.php' =>  array('Document','get','detail'),
			'detail-entite.php' =>  array('entite','get','detail'),
			'detail-several-document.php' =>  array('Document','get','detailAll'),
			'detail-utilisateur.php' =>  array('Utilisateur','get','detail'),

			'edit-connecteur-entite.php' =>  array('Connecteur','get','edit'),
			'edit-extension.php' =>  array('Extension','get','edit'),
			'external-data.php' =>  array('Document','get','externalData'),
			'journal.php' =>  array('Journal','get','list'),
			'list-connecteur-entite.php' =>  array('Connecteur/','get','list'),
			'list-document.php' =>  array('Document/','get','list'),

			'list-entite.php' =>  array('entite','get','list'),

			'list-extension.php' =>  array('Extension','get','list'),
			'list-flux-connecteur.php' =>  array('Connecteur/','get','recherche'),
			'list-role-utilisateur.php' =>  array('UtilisateurRole/','get','list'),
			'list-utilisateur.php' =>  array('Utilisateur','get','list'),
			'modif-connecteur-entite.php' =>  array('Connecteur/','get','edit'),
			'modif-document.php' =>  array('Document/','get','edit'),
			'modif-entite.php' =>  array('Entite/','get','edit'),
			'modif-utilisateur.php' =>  array('Utilisateur/','get','edit'),
			'receive-file.php' =>  array('Document/','get','receiveFile'),
			'recherche-document.php' =>  array('Document/','get','recherche'),
			'recuperation-fichier.php' =>  array('Document/','get','recuperationFichier'),
			'send-file.php' =>  array('Document/','get','sendFile')
		);

		if (empty($legacy_script[$old_script_name])){
			throw new Exception("Impossible de trouver le script $old_script_name");
		}

		return $legacy_script[$old_script_name];
	}


}