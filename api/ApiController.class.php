<?php

class ApiController {

	private $objectInstancier;

	public function __construct(ObjectInstancier $objectInstancier) {
		$this->objectInstancier = $objectInstancier;
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

		header_wrapper("Content-type: application/json; charset=utf-8");
		echo json_encode($result);
	}

	public function callMethod($controller,$action){
		/** @var BaseAPIControllerFactory $baseAPIControllerFactory */
		$baseAPIControllerFactory = $this->objectInstancier->getInstance('BaseAPIControllerFactory');
		$controllerObject = $baseAPIControllerFactory->getInstance($controller,$this->getUtilisateurId());

		$methode_name = "{$action}Action";

		if (! method_exists($controllerObject,$methode_name)){
			throw new Exception("Impossible de trouver l'action $controller::$action");
		}

		
		return $controllerObject->$methode_name();
	}

}