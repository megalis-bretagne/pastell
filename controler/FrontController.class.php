<?php

class FrontController {

	const PAGE_REQUEST = 'page_request';

	/** @var  Recuperateur */
	private $getParameter;

	/** @var  Recuperateur */
	private $postParameter;

	private $server_info;

	private $objectInstancier;

	public function __construct(ObjectInstancier $objectInstancier) {
		$this->setGetParameter(array());
		$this->setPostParameter(array());
		$this->setServerInfo(array());
		$this->objectInstancier = $objectInstancier;
	}

	public function setGetParameter(array $get_parameter){
		$this->getParameter = new Recuperateur($get_parameter);
	}

	public function setPostParameter(array $post_parameter){
		$this->postParameter = new Recuperateur($post_parameter);
	}

	public function setServerInfo(array $server_info){
		$this->server_info = $server_info;
	}

	public function dispatch(){
		$page_request = $this->getParameter->get(self::PAGE_REQUEST,'Document/index');
		
		$list = explode("/",$page_request);
		if (empty($list[1])){
			$list[1] = 'index';
		}
		try {
			$this->verifCSRF();
			$this->callMethod($list[0], $list[1]);
		} catch (PastellNotFoundException $e){
			/** @var AccueilControler $accueilControler */
			$accueilControler = $this->getController("Accueil");
			$accueilControler->setException($e);
			$accueilControler->notFoundAction();
		} catch (Exception $e){
			/** @var AccueilControler $accueilControler */
			$accueilControler = $this->getController("Accueil");
			$accueilControler->setException($e);
			$accueilControler->errorAction();
		}
	}


	private function getController($controller){
		$controller_name = "{$controller}Controler";
		if (! class_exists($controller_name)){
			throw new PastellNotFoundException("Impossible de trouver le controller $controller_name");
		}
		/** @var Controler $theController */
		$theController =  $this->objectInstancier->getInstance($controller_name);
		$theController->setServerInfo($this->server_info);
		$theController->setGetInfo($this->getParameter);
		$theController->setPostInfo($this->postParameter);
		return $theController;
	}

	private function callMethod($controller,$action){
		$controllerObject = $this->getController($controller);
		$controllerObject->_beforeAction();
		$methode_name = "{$action}Action";

		if (! method_exists($controllerObject,$methode_name)){
			throw new PastellNotFoundException("Impossible de trouver l'action $controller::$action");
		}

		return $controllerObject->$methode_name();
	}

	private function verifCSRF(){
		if (empty($this->server_info['REQUEST_METHOD'])){
			return true;
		}
		if ($this->server_info['REQUEST_METHOD'] != 'POST'){
			return true;
		}
		/** @var CSRFToken $csrfToken */
		$csrfToken = $this->objectInstancier->getInstance('CSRFToken');
		return $csrfToken->verifToken();
	}

}

class PastellNotFoundException extends Exception {};