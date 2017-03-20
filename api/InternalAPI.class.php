<?php

class InternalAPI {

	const CALLER_TYPE_NONE = "";
	const CALLER_TYPE_CONSOLE = "console";
	const CALLER_TYPE_WEBSERVICE = "webservice";
	const CALLER_TYPE_SCRIPT = "script";

	/** @var ObjectInstancier */
	private $objectInstancier;

	private $id_u;

	private $caller_type;

	private $fileUploader;

	public function __construct(ObjectInstancier $objectInstancier){
		$this->objectInstancier = $objectInstancier;
		$this->fileUploader = new FileUploader();
	}

	public function setUtilisateurId($id_u){
		$this->id_u = $id_u;
	}

	public function setCallerType($caller_type){
		$this->caller_type = $caller_type;
	}

	public function setFileUploader(FileUploader $fileUploader){
		$this->fileUploader = $fileUploader;
	}

	public function get($ressource,$data = array()){
		return $this->callMethod('get',$ressource,$data);
	}

	public function post($ressource,$data = array()){
		return $this->callMethod('post',$ressource,$data);
	}

	public function delete($ressource,$data = array()){
		return $this->callMethod('delete',$ressource,$data);
	}

	public function put($ressource,$data = array()){
		return $this->callMethod('put',$ressource,$data);
	}

	public function compatV1Edition($ressource,$data=array()){
		return $this->callMethod('compatV1Edition',$ressource,$data);
	}

	private function callMethod($request_method,$ressource,$data){
		$ressource = ltrim($ressource,"/");
		$query_arg = explode("/",$ressource);
		$controller_name = array_shift($query_arg);
		if (! $controller_name){
			throw new Exception("Ressource absente");
		}

		$controllerObject = $this->getInstance($controller_name,$data);
		$controllerObject->setQueryArgs($query_arg);
		$controllerObject->setCallerType($this->caller_type);
		return $controllerObject->$request_method();
	}

	private function getInstance($controllerName,$data = array()){
		$controller_name = ucfirst("{$controllerName}APIController");

		if (! class_exists($controller_name)){
			throw new NotFoundException("La ressource $controllerName n'a pas été trouvée");
		}

		/** @var BaseAPIController $controllerObject */
		$controllerObject = $this->objectInstancier->getInstance($controller_name);
		if (! $this->id_u && $this->caller_type != self::CALLER_TYPE_SCRIPT){
			throw new UnauthorizedException("Vous devez être connecté pour utiliser l'API");
		}
		if ($this->caller_type == self::CALLER_TYPE_SCRIPT){
			$controllerObject->setAllDroit(true);
		}
		$controllerObject->setUtilisateurId($this->id_u);
		$controllerObject->setRequestInfo($data);
		$controllerObject->setRoleUtilisateur($this->objectInstancier->getInstance('RoleUtilisateur'));
		$controllerObject->setFileUploader($this->fileUploader);

		return $controllerObject;
	}
}