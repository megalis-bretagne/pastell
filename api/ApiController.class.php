<?php

class ApiController {

	const PARAM_API_FUNCTION = 'api_function';

	private $objectInstancier;

	private $get;

	public function __construct(ObjectInstancier $objectInstancier) {
		$this->objectInstancier = $objectInstancier;
		$this->setGetArray($_GET);
	}

	public function setGetArray(array $get){
		$this->get = $get;
	}

	public function dispatch(){
		try {
			$this->dispatchThrow();
		} catch (Exception $e){
			$result['status'] = 'error';
			$result['error-message'] = $e->getMessage();
			$this->sendJson($result);
		}
	}

	private function getAPINameFromLegacyScript($old_script_name){
		$legacy_script = array(
			'action.php' => 'Document/action',
			'action-connecteur-entite.php' => 'Connecteur/doAction',
			'add-role-utilisateur.php' => 'UtilisateurRole/add',
			'add-several-role-utilisateur.php' => 'UtilisateurRole/add',
			'create-connecteur-entite.php' => 'Connecteur/create',
			'create-document.php' => 'Document/create',
			'create-entite.php' => 'Entite/create',
			'create-flux-connecteur.php' => 'Connecteur/associateFlux',
			'create-utilisateur.php' => 'Utilisateur/create',
			'delete-connecteur-entite.php' => 'Connecteur/delete',
			'delete-entite.php' => 'Entite/delete',
			'delete-extension.php' => 'Extension/delete',
			'delete-flux-connecteur.php' => 'Connecteur/deleteFluxConnecteur',
			'delete-role-utilisateur.php'=> 'UtilisateurRole/delete',
			'delete-several-roles-utilisateur.php' => 'UtilisateurRole/deleteSeveral',
			'delete-utilisateur.php' => 'Utilisateur/delete',
			'detail-connecteur-entite.php' => 'Connecteur/detail',
			'detail-document.php' => 'Document/detail',
			'detail-entite.php' => 'Entite/detail',
			'detail-several-document.php' => 'Document/detailAll',
			'detail-utilisateur.php' => 'Utilisateur/detail',
			'document-type-action.php' => 'DocumenType/action',
			'document-type-info.php' => 'DocumentType/info',
			'document-type.php' => 'DocumentType/list',
			'edit-connecteur-entite.php' => 'Connecteur/edit',
			'edit-extension.php' => 'Extension/edit',
			'external-data.php' => 'Document/externalData',
			'journal.php' => 'Journal/list',
			'list-connecteur-entite.php' => 'Connecteur/list',
			'list-document.php' => 'Document/list',
			'list-entite.php' => 'Entite/list',
			'list-extension.php' => 'Extension/list',
			'list-flux-connecteur.php' => 'Connecteur/recherche',
			'list-role-utilisateur.php' => 'UtilisateurRole/list',
			'list-roles.php' => 'Role/list',
			'list-utilisateur.php' => 'Utilisateur/list',
			'modif-connecteur-entite.php' => 'Connecteur/edit',
			'modif-document.php' => 'Document/edit',
			'modif-entite.php' => 'Entite/edit',
			'modif-utilisateur.php' => 'Utilisateur/edit',
			'receive-file.php' => 'Document/receiveFile',
			'recherche-document.php' => 'Document/recherche',
			'recuperation-fichier.php' => 'Document/recuperationFichier',
			'send-file.php' => 'Document/sendFile',
			'version.php' => 'Version/info',
		);

		if (empty($legacy_script[$old_script_name])){
			throw new Exception("Impossible de trouver le script $old_script_name");
		}

		return $legacy_script[$old_script_name];

	}

	private function dispatchThrow(){
		if (empty($this->get[self::PARAM_API_FUNCTION])){
			throw new Exception("Il faut spécifier une fonction de l'api");
		}
		$api_function = $this->get[self::PARAM_API_FUNCTION];

		if (preg_match("#.php$#",$api_function)){
			$api_function = $this->getAPINameFromLegacyScript($api_function);
		}
		if (preg_match("#rest/allo#",$api_function)){
			$api_function = "Version/allo";
		}

		$list = explode("/",$api_function);
		if (empty($list[1])){
			throw new Exception("Impossible de trouver l'action associée");
		}

		$this->callJson($list[0],$list[1]);
	}

	public function getUtilisateurId(){
		/** @var ApiAuthentication $apiAuthentication */
		$apiAuthentication = $this->objectInstancier->getInstance('ApiAuthentication');
		return $apiAuthentication->getUtilisateurId();
	}

	public function callJson($controller,$action){
		$result = array();
		try {
			$result = $this->callMethod($controller, $action);
		} catch(ApiAuthenticationException $e){
			header_wrapper('HTTP/1.1 401 Unauthorized');
			header_wrapper('WWW-Authenticate: Basic realm="API Pastell"');
			$result['status'] = 'error';
			$result['error-message'] = $e->getMessage();
		} catch (Exception $e){
			$result['status'] = 'error';
			$result['error-message'] = $e->getMessage();
		}

		$this->sendJson($result);
	}

	private function sendJson(array $result){
		header_wrapper("Content-type: application/json; charset=utf-8");
		$result_json =  json_encode($result);

		if ($result_json === false ){
			$result_error['status'] = 'error';
			$result_error['error-message'] = "Impossible d'encoder le résultat en JSON [code ".json_last_error()."]: "
				. json_last_error_msg();
			$result_json =  json_encode($result_error);
		}

		echo $result_json;
	}


	public function callMethod($controller,$action){
		/** @var BaseAPIControllerFactory $baseAPIControllerFactory */
		$baseAPIControllerFactory = $this->objectInstancier->getInstance('BaseAPIControllerFactory');
		$controllerObject = $baseAPIControllerFactory->getInstance($controller,$this->getUtilisateurId());
		$controllerObject->setCallerType('web service');

		$methode_name = "{$action}Action";

		if (! method_exists($controllerObject,$methode_name)){
			throw new Exception("Impossible de trouver l'action $controller::$action");
		}

		
		return $controllerObject->$methode_name();
	}

}