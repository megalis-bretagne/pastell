<?php
class LastErrorException extends Exception {}
class LastMessageException extends Exception {}

class Controler {

	private $objectInstancier;
	private $viewParameter;
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
		if (isset($this->$key)){
			//Ca ne peut jamais être appelé...
			return $this->$key;
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
		$this->$key  = $value;
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
	} //@codeCoverageIgnore

	/**
	 * @return Gabarit
	 */
	public function getGabarit(){
		return $this->getInstance("Gabarit");
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