<?php

use Sabre\DAV\Client;
/*
 * source doc:
 * http://sabre.io/dav/davclient/
 * https://www.ikeepincloud.com/fr/script_php
 */

class WebdavWrapper {

	private $lastError;
	private $dav;

	public function __construct($url, $user, $password){

		$settings = array(
				'baseUri' => $url,
				'userName' => $user,
				'password' => $password,
		);
		// Creation d'un nouveau client SabreDAV
		$this->dav = new Client($settings);
	}
	
	public function __destruct(){}
	
	public function getLastError(){
		return $this->lastError;
	}
	
	public function listFolder($folder){
	
		$nlist = $this->dav->propfind($folder, array(
				'{DAV:}displayname',
		),1);	
		if (!$nlist){
			return array();
		}	
		foreach($nlist as $file => $value){
			$result[] = basename($file);
		}	
		return $result;
	}
	
	public function createFolder($folder,$new_folder_name){
		$folder_list = $this->listFolder($folder);
		if (in_array($new_folder_name, $folder_list)) {
			return;
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
		$new_file = $folder."/".$remote_file;
		$folder_list = $this->listFolder($folder);
		if (in_array($remote_file, $folder_list)) {
			throw new Exception($remote_file." existe déja ".$folder);
		}
		return $this->dav->request('PUT', $new_file, $file_content);	
	}
}