<?php

use Symfony\Component\RateLimiter\Exception\RateLimitExceededException;

class HttpApi
{
    public const PARAM_API_FUNCTION = 'api_function';

    public const API_VERSION = 'v2';

    public static $HTTP_AUTHORIZED_METHOD =  array('get','post','patch','delete');

    /** @var JSONoutput */
    private $jsonOutput;

    private $objectInstancier;

    private $get = array();

    private $request = array();

    private $server = array();

    private $is_legacy = false;

    /** @var  Monolog\Logger */
    private $logger;

    public function __construct(ObjectInstancier $objectInstancier)
    {
        $this->objectInstancier = $objectInstancier;
        $this->jsonOutput = $objectInstancier->getInstance('JSONoutput');
        $this->logger = $this->objectInstancier->getInstance("Monolog\Logger");
    }

    public function setRequestArray(array $request)
    {
        $this->request = $request;
    }

    public function setGetArray(array $get)
    {
        $this->get = $get;
    }

    public function setServerArray(array $server)
    {
        $this->server = $server;
    }

    public function dispatch()
    {
        try {
            $this->dispatchThrow();
        } catch (BadRequestException $e) {
            header_wrapper('HTTP/1.1 400 Bad Request');
        } catch (UnauthorizedException $e) {
            header_wrapper('HTTP/1.1 401 Unauthorized');
            header_wrapper('WWW-Authenticate: Basic realm="API Pastell"');
        } catch (ForbiddenException $e) {
            header_wrapper('HTTP/1.1 403 Forbidden');
        } catch (NotFoundException $e) {
            header_wrapper('HTTP/1.1 404 Not Found');
        } catch (MethodNotAllowedException $e) {
            header_wrapper('HTTP/1.1 405 Method Not Allowed');
        } catch (ConflictException $e) {
            header_wrapper('HTTP/1.1 409 Conflict');
        } catch (RateLimitExceededException $e) {
            header_wrapper("HTTP/1.1 429 Too Many Requests");
            header_wrapper("Retry-after: 60");
        } catch (InternalServerException $e) {
            header_wrapper('HTTP/1.1 500 Internal Server Error');
        } catch (Exception $e) {
            if (! $this->is_legacy) {
                header_wrapper('HTTP/1.1 400 Bad Request');
            }
        } finally {
            if (isset($e)) {
                $result['status'] = 'error';
                $result['error-message'] = $e->getMessage();
                $this->jsonOutput->sendJson($result);
                $this->logger->addWarning(
                    "API call error  : {$result['error-message']}"
                );
            } else {
                $this->logger->addWarning(
                    "API call success"
                );
            }
        }
    }

    private function dispatchThrow()
    {
        $request_method = strtolower($this->server['REQUEST_METHOD']);

        if (! in_array($request_method, self::$HTTP_AUTHORIZED_METHOD)) {
            throw new MethodNotAllowedException("Cette méthode n'est pas utilisable sur l'API Pastell");
        }

        if (empty($this->get[self::PARAM_API_FUNCTION])) {
            throw new Exception("Il faut spécifier une fonction de l'api");
        }
        $api_function = $this->get[self::PARAM_API_FUNCTION];
        $api_function = ltrim($api_function, "/");

        $this->logger->addInfo(
            "Call $request_method $api_function",
            ['GET' => $this->get,'FILES' => $_FILES]
        );

        $is_legacy = false;
        $old_api_function = false;
        if (preg_match("#.php$#", $api_function)) {
            $old_api_function = $api_function;
            $old_info = $this->getAPINameFromLegacyScript($api_function);
            $api_function = "v2/" . $old_info[0];
            $request_method = $old_info[1];
            $is_legacy = true;
            $this->is_legacy = true;
            $this->logger->addInfo(
                "Call legacy API corresponding to > $request_method $api_function"
            );
        }



        if (preg_match("#rest/allo#", $api_function)) {
            $api_function = "v2/version/allo";
        }

        $list = explode("/", $api_function);
        $api_version = array_shift($list);

        if ($api_version != self::API_VERSION) {
            throw new Exception("Version de l'API incorrecte ou version absente");
        }
        $ressource = implode("/", $list);

        /** @var InternalAPI $internalAPI */
        $internalAPI = $this->objectInstancier->getInstance("InternalAPI");

        $utilisateur_id = $this->getUtilisateurId();

        $internalAPI->setUtilisateurId($utilisateur_id);
        $internalAPI->setCallerType(InternalAPI::CALLER_TYPE_WEBSERVICE);

        $this->objectInstancier->getInstance(Journal::class)->setId($utilisateur_id);

        if ($request_method == 'patch' && ! $is_legacy) {
            parse_str(file_get_contents("php://input"), $this->request);
        }

        if ($is_legacy) {
            $this->logger->debug("[Legacy API] $api_function", $_FILES);

            foreach ($_FILES as $index => $files) {
                if (is_array($_FILES[$index]['name'])) {
                    foreach ($_FILES[$index]['name'] as $i => $name) {
                        $_FILES[$index]['name'][$i] = utf8_encode($name);
                    }
                } else {
                    $_FILES[$index]['name'] = utf8_encode($files['name']);
                }
            }

            $fileUploader = $this->objectInstancier->getInstance('FileUploader');
            $fileUploader->setFiles($_FILES);
            $internalAPI->setFileUploader($fileUploader);
            $this->request = utf8_encode_array($this->request);
        }
        $result = $internalAPI->$request_method($ressource, $this->request);
        if (in_array($request_method, array('post')) && ! $is_legacy) {
            header_wrapper('HTTP/1.1 201 Created');
        }
        if ($is_legacy) {
            if ($old_api_function == 'action.php' && $result['result'] === true) {
                $result['result'] = "1";
            }
            $result = $this->string_encode_array($result);
        }

        $this->jsonOutput->sendJson($result, $is_legacy ? false : true);
        $this->logger->addDebug(
            "API result : " . json_encode($result)
        );
    }

    private function string_encode_array($array)
    {
        if (! is_array($array) && !is_object($array)) {
            return strval($array);
        }
        $result = array();
        foreach ($array as $cle => $value) {
            $result[strval($cle)] = $this->string_encode_array($value);
        }
        return $result;
    }

    public function getUtilisateurId()
    {
        /** @var ApiAuthentication $apiAuthentication */
        $apiAuthentication = $this->objectInstancier->getInstance('ApiAuthentication');
        return $apiAuthentication->getUtilisateurId();
    }

    public function getAPINameFromLegacyScript($old_script_name)
    {
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

            'list-connecteur-entite.php' => array("entite/{$this->getFromRequest('id_e')}/connecteur", 'get'),
            'detail-connecteur-entite.php' => array("entite/{$this->getFromRequest('id_e')}/connecteur/{$this->getFromRequest('id_ce')}", 'get'),
            'delete-connecteur-entite.php' => array("entite/{$this->getFromRequest('id_e')}/connecteur/{$this->getFromRequest('id_ce')}", 'delete'),
            'modif-connecteur-entite.php' => array("entite/{$this->getFromRequest('id_e')}/connecteur/{$this->getFromRequest('id_ce')}", 'patch'),
            'edit-connecteur-entite.php' => array("entite/{$this->getFromRequest('id_e')}/connecteur/{$this->getFromRequest('id_ce')}/content", 'patch'),

            'create-connecteur-entite.php' => array("entite/{$this->getFromRequest('id_e')}/connecteur/{$this->getFromRequest('id_ce')}", 'post'),
            'action-connecteur-entite.php' =>
                array(
                    "entite/{$this->getFromRequest('id_e')}/flux/{$this->getFromRequest('flux')}/action",
                    'post'
                ),

            'create-flux-connecteur.php' => array("entite/{$this->getFromRequest('id_e')}/flux/{$this->getFromRequest('flux')}/connecteur/{$this->getFromRequest('id_ce')}?type={$this->getFromRequest('type')}", 'post'),
            'delete-flux-connecteur.php' => array("entite/{$this->getFromRequest('id_e')}/flux/{$this->getFromRequest('id_fe')}", 'delete'),


            'list-flux-connecteur.php' => array(
                "/entite/{$this->getFromRequest('id_e')}/flux?type={$this->getFromRequest('type')}&flux={$this->getFromRequest('flux')}",
                'get'
            ),

            'list-document.php' => array("entite/{$this->getFromRequest('id_e')}/document", 'get'),
            'detail-document.php' => array("entite/{$this->getFromRequest('id_e')}/document/{$this->getFromRequest('id_d')}", 'get'),
            'detail-several-document.php' => array("entite/{$this->getFromRequest('id_e')}/document/", 'get'),

            'create-document.php' =>  array("entite/{$this->getFromRequest('id_e')}/document", 'post'),
            'modif-document.php' => array("entite/{$this->getFromRequest('id_e')}/document/{$this->getFromRequest('id_d')}", 'patch'),
            'recherche-document.php' => array("entite/{$this->getFromRequest('id_e')}/document", 'get'),

            'external-data.php' => array("entite/{$this->getFromRequest('id_e')}/document/{$this->getFromRequest('id_d')}/externalData/{$this->getFromRequest('field')}", 'get'),
            'recuperation-fichier.php' => array("entite/{$this->getFromRequest('id_e')}/document/{$this->getFromRequest('id_d')}/file/{$this->getFromRequest('field')}/{$this->getFromRequest('num')}", 'get'),

            ## oops : field => field_name num=>file_number pour faire comme en V1

            'receive-file.php' => array("entite/{$this->getFromRequest('id_e')}/document/{$this->getFromRequest('id_d')}/file/{$this->getFromRequest('field_name')}/{$this->getFromRequest('file_number')}?receive=true", 'get'),

            'send-file.php' => array("entite/{$this->getFromRequest('id_e')}/document/{$this->getFromRequest('id_d')}/file/{$this->getFromRequest('field')}/{$this->getFromRequest('num')}", 'post'),
            'action.php' => array("entite/{$this->getFromRequest('id_e')}/document/{$this->getFromRequest('id_d')}/action/{$this->getFromRequest('action')}", 'post'),

        );
        if (empty($legacy_script[$old_script_name])) {
            throw new NotFoundException("Impossible de trouver le script $old_script_name");
        }

        return $legacy_script[$old_script_name];
    }

    private function getFromRequest($key, $default = false)
    {
        if (empty($this->request[$key])) {
            return $default;
        }
        if (is_array($this->request[$key])) {
            return '';
        }
        return $this->request[$key];
    }
}
