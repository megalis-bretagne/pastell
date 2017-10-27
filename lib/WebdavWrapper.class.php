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
	private $dav;

	public function setDataConnexion($url, $user, $password){
        $settings = array(
            'baseUri' => $url,
            'userName' => $user,
            'password' => $password,
        );
        // Creation d'un nouveau client SabreDAV
        $this->dav = new Client($settings);
    }

	public function getLastError(){
		return $this->lastError;
	}

	public function exists($element){
	    try {
            $this->dav->propfind($element, array(
                '{DAV:}displayname',
            ), 1);
        } catch (\Sabre\HTTP\ClientHttpException $e){
	        if ($e->getCode() == '404'){
	            return false;
            }
            throw new Exception($e->getCode(). " ".$e->getMessage(),$e);
        }
        return true;
    }

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
	
	public function createFolder($folder,$new_folder_name){
		$folder_list = $this->listFolder($folder);
		if (in_array($new_folder_name, $folder_list)) {
			return false;
		}
		return $this->dav->request('MKCOL', $new_folder_name);
	}
	
	public function delete($folder,$ficrep){
		$folder_list = $this->listFolder($folder);
		if (in_array($ficrep, $folder_list)) {
			return $this->dav->request('DELETE',$folder."/".$ficrep);
		}
		else {
			throw new Exception($ficrep." n'est pas dans ".$folder);
		}
	}
	
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