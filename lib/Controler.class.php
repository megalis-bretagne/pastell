<?php

class Controler {

	private $objectInstancier;
	private $viewParameter;

	/**
	 * @var string
	 * @deprecated Use getLastError()/setLastError() instead
	 * Je pense que c'est pas utilisé vu qu'on passe par les objet LastError et LastMessage
	 */
	protected $lastError;

	private $dont_redirect = false;

	private $server_info;
	/** @var  Recuperateur */
	private $getInfo;
	/** @var  Recuperateur */
	private $postInfo;

	public function __construct(ObjectInstancier $objectInstancier){
		$this->objectInstancier = $objectInstancier;
		$this->viewParameter = array();
		$this->setGetInfo(new Recuperateur(array()));
		$this->setPostInfo(new Recuperateur(array()));
	}


	public function _beforeAction(){}

	public function setServerInfo(array $server_info){
		$this->server_info = $server_info;
	}

	public function getServerInfo($key){
		return $this->getFormArray($this->server_info,$key);
	}

	public function setGetInfo(Recuperateur $getInfo){
		$this->getInfo = $getInfo;
	}

	public function getGetInfo(){
		return $this->getInfo;
	}

	public function setPostInfo(Recuperateur $postInfo){
		$this->postInfo = $postInfo;
	}

	public function getPostInfo(){
		return $this->postInfo;
	}

	public function getPostOrGetInfo(){
        if ($this->getServerInfo('REQUEST_METHOD') == 'POST'){
            return $this->getPostInfo();
        } else {
            return $this->getGetInfo();
        }
    }

	private function getFormArray($array,$key){
		if (empty($array[$key])){
			return false;
		}
		return $array[$key];
	}


	public function setDontRedirect($dont_redirect){
		$this->dont_redirect = $dont_redirect;		
	}
	
	public function isDontRedirect(){
		return $this->dont_redirect;
	}


	/**
	 * @return LastMessage
	 */
	public function getLastMessage(){
		return $this->getObjectInstancier()->getInstance('LastMessage');
	}

	/**
	 * @return LastError
	 */
	public function getLastError(){
		return $this->getObjectInstancier()->getInstance('LastError');
	}

	public function setLastError($message){
		/** @var LastError $lastError */
		$lastError = $this->getObjectInstancier()->getInstance('LastError');
		$lastError->setLastError($message);
	}

	public function setLastMessage($message){
		/** @var LastMessage $lastMessage */
		$lastMessage = $this->getObjectInstancier()->getInstance('LastMessage');
		$lastMessage->setLastMessage($message);
	}


	public function __get($key){

		if ($this->isViewParameter($key)){

			return $this->viewParameter[$key];
		}
		return $this->objectInstancier->$key;
	}

	public function getObjectInstancier(){
		return $this->objectInstancier;
	}

	public function getInstance($class_name){
		return $this->getObjectInstancier()->getInstance($class_name);
	}

	public function __set($key,$value){
		$this->setViewParameter($key,$value);
	}
	
	public function setViewParameter($key,$value){
		$this->viewParameter[$key] = $value;
		//$this->$key  = $value;
	}
	
	public function setAllViewParameter(array $viewParameter){
		$this->viewParameter = $viewParameter;
	}
	
	public function getViewParameter(){
		return $this->viewParameter;
	}

	public function isViewParameter($key){
		return isset($this->viewParameter[$key]);
	}
	
	public function exitToIndex(){
		$this->doRedirect($this->getObjectInstancier()->{'site_index'});
	}

	/**
	 * @param string $to
	 * @throws LastErrorException
	 * @throws LastMessageException
	 */
	public function redirect($to = ""){
		$url = rtrim(SITE_BASE,"/")."/".ltrim($to,"/");
		$this->doRedirect($url);
	}

	public function absoluteRedirect($url){
		$this->doRedirect($url);
	}

	private function doRedirect($url){
		if ($this->isDontRedirect()){
			$error = $this->getLastError()->getLastError();
			$this->getLastError()->setLastMessage(false);
			if ($error){
				throw new LastErrorException("Redirection vers $url : $error");
			} else {
				$message = $this->getLastMessage()->getLastMessage();
				$this->getLastMessage()->setLastMessage(false);
				throw new LastMessageException("Redirection vers $url: $message");
			}
		}
		header_wrapper("Location: $url");
		exit_wrapper();
	}

	/**
	 * @return Gabarit
	 */
	public function getGabarit(){
		return $this->getInstance(Gabarit::class);
	}

	public function renderDefault(){
		$this->getGabarit()->setParameters($this->getViewParameter());
		$this->getGabarit()->render("Page");
	}
	
	public function render($template){
		$this->getGabarit()->setParameters($this->getViewParameter());
		$this->getGabarit()->render($template);
	}

}
