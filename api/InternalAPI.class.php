<?php

class InternalAPI {

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
		$ressource = ltrim($ressource,"/");
		$list = explode("/",$ressource);
		$controller_name = array_shift($list);
		if (! $controller_name){
			throw new Exception("Ressource absente");
		}

		return $this->callMethod($controller_name,$list,'get',$data);
	}

	private function callMethod($controller,array $query_arg,$request_method,$data){
		$controllerObject = $this->getInstance($controller,$data);

		$controllerObject->setQueryArgs($query_arg);

		$controllerObject->setCallerType($this->caller_type);

		if (! method_exists($controllerObject,$request_method)){
			throw new MethodNotAllowedException("La méthode $request_method n'est pas disponible pour l'objet $controller");
		}

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