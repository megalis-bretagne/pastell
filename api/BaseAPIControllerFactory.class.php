<?php

class BaseAPIControllerFactory {

	private $objectInstancier;

	private $request;

	public function __construct(ObjectInstancier $objectInstancier) {
		$this->objectInstancier = $objectInstancier;
		$this->setRequest($_REQUEST);
	}

	public function setRequest($request){
		$this->request = $request;
	}

	public function getInstance($controllerName,$id_u){
		$controller_name = "{$controllerName}Controller";

		if (! class_exists($controller_name)){
			throw new Exception("Impossible de trouver le controller $controllerName");
		}

		/** @var BaseAPIController $controllerObject */
		$controllerObject = $this->objectInstancier->getInstance($controller_name);

		$controllerObject->setUtilisateurId($id_u);
		$controllerObject->setRequestInfo($this->request);
		$controllerObject->setRoleUtilisateur($this->objectInstancier->getInstance('RoleUtilisateur'));
		return $controllerObject;

	}

}