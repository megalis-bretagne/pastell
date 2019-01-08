<?php

use Sabre\DAV\Client;
/*
 * source doc:
 * http://sabre.io/dav/davclient/
 * https://www.ikeepincloud.com/fr/script_php
 */

// Un docker pour tester webdav : https://hub.docker.com/r/morrisjobke/webdav/
//

class WebdavWrapper {

	private $lastError;

	/** @var Client */
	private $dav;

	/** @var WebdavClientFactory */
	private $webdavClientFactory;

	public function __construct() {
		$this->setWebdavClientFactory(new WebdavClientFactory());
	}

	public function setWebdavClientFactory(WebdavClientFactory $webdavClientFactory){
		$this->webdavClientFactory = $webdavClientFactory;
	}

	public function setDataConnexion($url, $user, $password){
        $settings = array(
            'baseUri' => $url,
            'userName' => $user,
            'password' => $password,
        );
        // Creation d'un nouveau client SabreDAV
        $this->dav = $this->webdavClientFactory->getInstance($settings);
    }

	public function getLastError(){
		return $this->lastError;
	}

    /**
     * Authenticate with certificate
     *
     * @param string $certificatePath
     * @param string $keyPath
     * @param string $certificatePassword
     */
    public function setAuthenticationByCertificate($certificatePath, $keyPath, $certificatePassword) {
        $this->dav->addCurlSetting(CURLOPT_SSLCERT, $certificatePath);
        $this->dav->addCurlSetting(CURLOPT_SSLKEY, $keyPath);
        $this->dav->addCurlSetting(CURLOPT_SSLKEYPASSWD, $certificatePassword);
    }

    /**
     * @see http://sabre.io/dav/davclient/
     * If the server was not a WebDAV server, the response will be empty.
     *
     * @return bool
     */
    public function isConnected() {
        return !empty($this->dav->options());
    }

	/**
	 * @param $element
	 * @return bool
	 * @throws Exception
	 */
	public function exists($element){
	    try {
            /**
             * Only check the current resource
             * @see http://www.webdav.org/specs/rfc4918.html#HEADER_Depth
             */
            $this->dav->propfind($element, array(
                '{DAV:}displayname',
            ), 0);
        } catch (\Sabre\HTTP\ClientHttpException $e){
	        if ($e->getCode() == '404'){
	            return false;
            }
            throw new Exception($e->getCode(). " ".$e->getMessage(),$e->getCode(),$e);
        }
        return true;
    }

	/**
	 * @param $folder
	 * @return array
	 * @throws \Sabre\HTTP\ClientHttpException
	 */
	public function listFolder($folder){

        $nlist = $this->dav->propfind($folder, array(
            '{DAV:}displayname',
        ), 1);

		if (!$nlist){
			return array();
		}
		$result = array();
		foreach($nlist as $file => $value){
			$result[] = basename($file);
		}
		return $result;
	}

	/**
	 * @param $folder
	 * @param $new_folder_name
	 * @return array|bool
	 * @throws \Sabre\HTTP\ClientHttpException
	 */
	public function createFolder($folder,$new_folder_name){
		$folder_list = $this->listFolder($folder);
		if (in_array($new_folder_name, $folder_list)) {
			return false;
		}
		return $this->dav->request('MKCOL', $new_folder_name);
	}

	/**
	 * @param $folder
	 * @param $ficrep
	 * @return array
	 * @throws \Sabre\HTTP\ClientHttpException
	 * @throws Exception
	 */
	public function delete($folder,$ficrep){
		$folder_list = $this->listFolder($folder);
		if (in_array($ficrep, $folder_list)) {
			return $this->dav->request('DELETE',$folder."/".$ficrep);
		}
		else {
			throw new Exception($ficrep." n'est pas dans ".$folder);
		}
	}

	/**
	 * @param $folder
	 * @param $remote_file
	 * @param $file_content
	 * @return array
	 * @throws \Sabre\HTTP\ClientHttpException
	 * @throws Exception
	 */
	public function addDocument($folder,$remote_file,$file_content){
	    if ($folder) {
            $new_file = $folder . "/" . $remote_file;
        } else {
	        $new_file = $remote_file;
        }

		$folder_list = $this->listFolder($folder);
		if (in_array($remote_file, $folder_list)) {
			throw new Exception($remote_file." existe déja ".$folder);
		}

		$response =  $this->dav->request('PUT', $new_file, $file_content);
        if ($response['statusCode'] != 201){
            throw new Exception("Erreur lors du dépot webdav : code ".$response['statusCode']);
        }
		return $response;

	}



}