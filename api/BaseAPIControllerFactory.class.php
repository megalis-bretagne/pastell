<?php

class BaseAPIControllerFactory {

	private $objectInstancier;

	private $request;

	private $fileUploader;

	public function __construct(ObjectInstancier $objectInstancier) {
		$this->objectInstancier = $objectInstancier;
		$this->setRequest($_REQUEST);
		$this->setFileUploader(new FileUploader());
	}

	public function setRequest($request){
		$this->request = $request;
	}

	public function setFileUploader(FileUploader $fileUploader){
		$this->fileUploader = $fileUploader;
	}

	public function getInstance($controllerName,$id_u){
		$controller_name = "{$controllerName}APIController";

		if (! class_exists($controller_name)){
			throw new Exception("Impossible de trouver le controller $controllerName");
		}

		/** @var BaseAPIController $controllerObject */
		$controllerObject = $this->objectInstancier->getInstance($controller_name);

		$controllerObject->setUtilisateurId($id_u);
		$controllerObject->setRequestInfo($this->request);
		$controllerObject->setRoleUtilisateur($this->objectInstancier->getInstance('RoleUtilisateur'));
		$controllerObject->setFileUploader($this->fileUploader);
		
		return $controllerObject;

	}

}