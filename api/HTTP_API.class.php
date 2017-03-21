<?php

class HTTP_API {

	const PARAM_API_FUNCTION = 'api_function';

	const API_VERSION = 'v2';

	public static $HTTP_AUTHORIZED_METHOD =  array('get','post','patch','delete');

	/** @var JSONoutput */
	private $jsonOutput;

	private $objectInstancier;

	private $get = array();

	private $request = array();

	private $server = array();

	public function __construct(ObjectInstancier $objectInstancier) {
		$this->objectInstancier = $objectInstancier;
		$this->jsonOutput = $objectInstancier->getInstance('JSONoutput');
	}

	public function setRequestArray(array $request){
		$this->request = $request;
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
		} catch(ForbiddenException $e){
			header_wrapper('HTTP/1.1 403 Forbidden');
		} catch(NotFoundException $e){
			header_wrapper('HTTP/1.1 404 Not Found');
		} catch (MethodNotAllowedException $e) {
			header_wrapper('HTTP/1.1 405 Method Not Allowed');
		} catch (ConflictException $e) {
			header_wrapper('HTTP/1.1 409 Conflict');
		} catch (InternalServerException $e){
			header_wrapper('HTTP/1.1 500 Internal Server Error');
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
		$api_function = ltrim($api_function,"/");

		if (preg_match("#.php$#",$api_function)){
			$old_info = $this->getAPINameFromLegacyScript($api_function);
			$api_function = "v2/".$old_info[0];
			$request_method = $old_info[1];
		}

		if (preg_match("#rest/allo#",$api_function)){
			$api_function = "v2/version/allo";
		}

		$list = explode("/",$api_function);
		$api_version = array_shift($list);

		if ($api_version != self::API_VERSION){
			throw new Exception("Version de l'API incorrecte ou version absente");
		}
		$ressource = implode("/",$list);

		/** @var InternalAPI $internalAPI */
		$internalAPI = $this->objectInstancier->getInstance("InternalAPI");

		$internalAPI->setUtilisateurId($this->getUtilisateurId());
		$internalAPI->setCallerType(InternalAPI::CALLER_TYPE_WEBSERVICE);

		if ($request_method == 'patch') {
			parse_str(file_get_contents("php://input"), $this->request);
		}

		$result = $internalAPI->$request_method($ressource, $this->request);

		if (in_array($request_method,array('post'))){
			header_wrapper('HTTP/1.1 201 Created');
		}
		$this->jsonOutput->sendJson($result,true);
	}

	public function getUtilisateurId(){
		/** @var ApiAuthentication $apiAuthentication */
		$apiAuthentication = $this->objectInstancier->getInstance('ApiAuthentication');
		return $apiAuthentication->getUtilisateurId();
	}

	public function getAPINameFromLegacyScript($old_script_name) {
		$legacy_script = array(
			'version.php' => array('version', 'get'),

			'list-roles.php' => array('role', 'get'),

			'document-type.php' => array('flux', 'get'),
			'document-type-info.php' => array("flux/{$this->getFromRequest('type')}", 'get'),
			'document-type-action.php' => array("flux/{$this->getFromRequest('type')}/action", 'get'),

			'list-extension.php' => array('extension', 'get'),
			'edit-extension.php' => array('extension', 'compatV1Edition'),
			'delete-extension.php' => array("extension/{$this->getFromRequest('id_extension')}", 'delete'),
			'journal.php' => array('journal', 'get'),
			'list-utilisateur.php' => array('utilisateur', 'get'),
			'detail-utilisateur.php' => array("utilisateur/{$this->getFromRequest('id_u')}", 'get'),
			'delete-utilisateur.php' => array("utilisateur/{$this->getFromRequest('id_u')}", 'delete'),
			'modif-utilisateur.php' => array("utilisateur/{$this->getFromRequest('id_u')}", 'patch', 'edit'),
			'create-utilisateur.php' => array('utilisateur', 'post'),
			'list-role-utilisateur.php' => array("utilisateur/{$this->getFromRequest('id_u')}/role", 'get'),
			'delete-role-utilisateur.php' => array("utilisateur/{$this->getFromRequest('id_u')}/role",'delete'),
			'add-role-utilisateur.php' => array("utilisateur/{$this->getFromRequest('id_u')}/role", 'post'),
			'add-several-role-utilisateur.php' => array("utilisateur/{$this->getFromRequest('id_u')}/role/add",'compatV1Edition'),
			'delete-several-roles-utilisateur.php' => array("utilisateur/{$this->getFromRequest('id_u')}/role/delete", 'compatV1Edition'),

			'list-entite.php' => array('entite', 'get'),
			'detail-entite.php' => array("entite/{$this->getFromRequest('id_e')}", 'get'),
			'modif-entite.php' => array("entite/{$this->getFromRequest('id_e')}", 'patch'),
			'delete-entite.php' => array("entite/{$this->getFromRequest('id_e')}", 'delete'),
			'create-entite.php' => array('entite', 'post'),


			//TODO

			'action.php' => array('document', 'get', 'action'),
			'action-connecteur-entite.php' => array('connecteur', 'get', 'doAction'),
			'create-connecteur-entite.php' => array('Connecteur', 'get', 'create'),
			'create-document.php' => array('document', 'get', 'create'),
			'create-flux-connecteur.php' => array('connecteur', 'get', 'associateFlux'),
			'delete-connecteur-entite.php' => array('Connecteur', 'get', 'delete'),
			'delete-flux-connecteur.php' => array('Connecteur', 'get', 'deleteFluxConnecteur'),


			'detail-connecteur-entite.php' => array('Connecteur', 'get', 'detail'),
			'detail-document.php' => array('Document', 'get', 'detail'),
			'detail-several-document.php' => array('Document', 'get', 'detailAll'),
			'edit-connecteur-entite.php' => array('Connecteur', 'get', 'edit'),
			'external-data.php' => array('Document', 'get', 'externalData'),
			'list-connecteur-entite.php' => array('Connecteur/', 'get', 'list'),
			'list-document.php' => array('Document/', 'get', 'list'),
			'list-flux-connecteur.php' => array('Connecteur/', 'get', 'recherche'),
			'modif-connecteur-entite.php' => array('Connecteur/', 'get', 'edit'),
			'modif-document.php' => array('Document/', 'get', 'edit'),
			'receive-file.php' => array('Document/', 'get', 'receiveFile'),
			'recherche-document.php' => array('Document/', 'get', 'recherche'),
			'recuperation-fichier.php' => array('Document/', 'get', 'recuperationFichier'),
			'send-file.php' => array('Document/', 'get', 'sendFile')
		);
		if (empty($legacy_script[$old_script_name])){
			throw new NotFoundException("Impossible de trouver le script $old_script_name");
		}

		return $legacy_script[$old_script_name];
	}

	public function getFromRequest($key,$default = false){
		if (empty($this->request[$key])){
			return $default;
		}
		return $this->request[$key];
	}

}

