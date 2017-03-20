<?php

class ApiController {

	const PARAM_API_FUNCTION = 'api_function';

	const API_VERSION = 'v2';

	public static $HTTP_AUTHORIZED_METHOD =  array('get','post','put','delete');

	/** @var JSONoutput */
	private $jsonOutput;

	private $objectInstancier;

	private $get;

	private $server;

	public function __construct(ObjectInstancier $objectInstancier) {
		$this->objectInstancier = $objectInstancier;
		$this->setGetArray($_GET);
		$this->setServerArray($_SERVER);
		$this->jsonOutput = $objectInstancier->getInstance('JSONoutput');
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
		} catch(UnauthorizedException $e) {
			header_wrapper('HTTP/1.1 401 Unauthorized');
			header_wrapper('WWW-Authenticate: Basic realm="API Pastell"');
		} catch(NotFoundException $e){
			header_wrapper('HTTP/1.1 404 Not Found');
		} catch (MethodNotAllowedException $e){
			header_wrapper('HTTP/1.1 405 Method Not Allowed');
		} catch (Exception $e){
			header_wrapper('HTTP/1.1 400 Bad Request');
		} finally {
			if (isset($e)) {
				$result['status'] = 'error';
				$result['error-message'] = $e->getMessage();
				$this->jsonOutput->sendJson($result);
			}
		}
	}

	private function dispatchThrow(){
		$request_method = strtolower($this->server['REQUEST_METHOD']);

		if (! in_array($request_method,self::$HTTP_AUTHORIZED_METHOD)){
			throw new MethodNotAllowedException("Cette méthode n'est pas utilisable sur l'API Pastell");
		}

		if (empty($this->get[self::PARAM_API_FUNCTION])){
			throw new Exception("Il faut spécifier une fonction de l'api");
		}
		$api_function = $this->get[self::PARAM_API_FUNCTION];

		if (preg_match("#.php$#",$api_function)){
			/** @var ApiV1Controller $apiV1Controller */
			$apiV1Controller = $this->objectInstancier->getInstance('ApiV1Controller');
			$apiV1Controller->go($api_function);
			return;
		}

		if (preg_match("#rest/allo#",$api_function)){
			$api_function = "v2/version/allo";
		}

		$list = explode("/",$api_function);
		if (array_shift($list) != self::API_VERSION){
			throw new Exception("Impossible de trouver la version de l'API");
		}
		$controller_name = array_shift($list);
		if (! $controller_name){
			throw new Exception("Il faut spécifier une fonction de l'api");
		}

		$this->callJson($controller_name,$list,$request_method);
	}

	public function getUtilisateurId(){
		/** @var ApiAuthentication $apiAuthentication */
		$apiAuthentication = $this->objectInstancier->getInstance('ApiAuthentication');
		return $apiAuthentication->getUtilisateurId();
	}

	public function callJson($controller,array $query_arg = array(),$request_method='get'){
		$result = $this->callMethod($controller, $query_arg,$request_method);
		$this->jsonOutput->sendJson($result);
	}

	public function callMethod($controller,array $query_arg = array(),$request_method = 'get'){
		/** @var BaseAPIControllerFactory $baseAPIControllerFactory */
		$baseAPIControllerFactory = $this->objectInstancier->getInstance('BaseAPIControllerFactory');
		return $baseAPIControllerFactory->callMethod($controller,$query_arg,$request_method);
	}

}

