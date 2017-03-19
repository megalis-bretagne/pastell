<?php

class ApiController {

	const PARAM_API_FUNCTION = 'api_function';

	const API_VERSION = 'v2';

	private $objectInstancier;

	private $get;

	private $server;

	public function __construct(ObjectInstancier $objectInstancier) {
		$this->objectInstancier = $objectInstancier;
		$this->setGetArray($_GET);
		$this->setServerArray($_SERVER);
	}

	public function setGetArray(array $get){
		$this->get = $get;
	}

	public function setServerArray(array $server){
		$this->server = $server;
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
			'version.php' => 'version',
		);

		if (empty($legacy_script[$old_script_name])){
			throw new Exception("Impossible de trouver le script $old_script_name");
		}

		return $legacy_script[$old_script_name];

	}

	private function getRequestMethodFromLegacySript($old_script_name,$request_method){
		$legacy_script_list = array(
			'version.php' => 'GET',
		);
		if (isset($legacy_script_list[$old_script_name])){
			return $legacy_script_list[$old_script_name];
		}
		return $request_method;
	}

	private function dispatchThrow(){
		$request_method = $this->server['REQUEST_METHOD'];


		if (empty($this->get[self::PARAM_API_FUNCTION])){
			throw new Exception("Il faut spécifier une fonction de l'api");
		}
		$api_function = $this->get[self::PARAM_API_FUNCTION];

		if (preg_match("#.php$#",$api_function)){
			$request_method = $this->getRequestMethodFromLegacySript($api_function,$request_method);
			$api_function = "v2/".$this->getAPINameFromLegacyScript($api_function);
		}
		if (preg_match("#rest/allo#",$api_function)){
			$api_function = "v2/version";
		}

		$list = explode("/",$api_function);
		if ($list[0] != self::API_VERSION){
			throw new Exception("Impossible de trouver la version de l'API");
		}
		if (empty($list[1])){
			throw new Exception("Il faut spécifier une fonction de l'api");
		}
		if (empty($list[2])){
			$list[2] = false;
		}


		$this->callJson($list[1],$list[2],$request_method);
	}

	public function getUtilisateurId(){
		/** @var ApiAuthentication $apiAuthentication */
		$apiAuthentication = $this->objectInstancier->getInstance('ApiAuthentication');
		return $apiAuthentication->getUtilisateurId();
	}

	public function callJson($controller,$action,$request_method){
		$result = array();
		try {
			$result = $this->callMethod($controller, $action,$request_method);
		} catch(ApiAuthenticationException $e) {
			header_wrapper('HTTP/1.1 401 Unauthorized');
			header_wrapper('WWW-Authenticate: Basic realm="API Pastell"');
			/*$result['status'] = 'error';
			$result['error-message'] = $e->getMessage();*/
		} catch (MethodNotAllowedException $e){
			header_wrapper('HTTP/1.1 405 Method Not Allowed');
			/*$result['status'] = 'error';
			$result['error-message'] = $e->getMessage();*/
		} catch (Exception $e){
			header_wrapper('HTTP/1.1 400 Bad Request');

		} finally {
			if (isset($e)) {
				$result['status'] = 'error';
				$result['error-message'] = $e->getMessage();
			}
		}

		$this->sendJson($result);
	}

	private function sendJson(array $result){
		header_wrapper("Content-type: application/json; charset=utf-8");
		$result_json =  json_encode($result,JSON_PRETTY_PRINT);

		if ($result_json === false ){
			$result_error['status'] = 'error';
			$result_error['error-message'] = "Impossible d'encoder le résultat en JSON [code ".json_last_error()."]: "
				. json_last_error_msg();
			$result_json =  json_encode($result_error, JSON_PRETTY_PRINT);
		}

		echo $result_json;
	}

	public function callMethod($controller,$action,$request_method){
		/** @var BaseAPIControllerFactory $baseAPIControllerFactory */
		$baseAPIControllerFactory = $this->objectInstancier->getInstance('BaseAPIControllerFactory');
		$controllerObject = $baseAPIControllerFactory->getInstance($controller,$this->getUtilisateurId());

		$methode_name = "{$action}Action";
		$controllerObject->setMethodName($methode_name);

		$controllerObject->setCallerType('web service');


		if (! method_exists($controllerObject,$request_method)){
			throw new MethodNotAllowedException("La méthode $request_method n'est pas disponible pour l'objet $controller");
		}

		return $controllerObject->$request_method();
	}

}

class MethodNotAllowedException extends Exception{}