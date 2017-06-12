<?php

//CMIS 1.0 avec binding ATOM
/*
 CMIS 1.0

For Alfresco 3.x : http://[host]:[port]/alfresco/service/cmis

For Alfresco 4.0.x and Alfresco 4.1.x : http://[host]:[port]/alfresco/cmisatom

For Alfresco 4.2 and Alfresco 5.0: http://[host]:[port]/alfresco/api/-default-/public/cmis/versions/1.0/atom

For Nuxeo : http://localhost:8080/nuxeo/atom/cmis

 */

class CMISWrapper {

	const NS_CMIS  = "http://docs.oasis-open.org/ns/cmis/core/200908/";
	const NS_CMIS_RA = "http://docs.oasis-open.org/ns/cmis/restatom/200908/";
	const NS_APP = "http://www.w3.org/2007/app";
	const NS_ATOM = "http://www.w3.org/2005/Atom";

	private $login;
	private $password;

	public function __construct($login,$password){
		$this->login = $login;
		$this->password = $password;
	}

	public function getRepositoryRetrieveInfo(){
		return array('repositoryId','repositoryName','repositoryDescription','vendorName','productName','productVersion','rootFolderId');
	}

	public function getFolderRetrieveInfo(){
		return array('content','id','summary','title','published','updated');
	}

	private function get($url,$content = false){
		$session = curl_init($url);
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_USERPWD, $this->login . ":" . $this->password);

		if ($content){
			curl_setopt($session, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($session, CURLOPT_POSTFIELDS, $content);
			curl_setopt($session, CURLOPT_HTTPHEADER, array ("Content-Type: application/atom+xml;type=entry"));
            //Possible correction pour fonctionnement HTTPS
			//curl_setopt($session, CURLOPT_POSTREDIR, 3);
            //curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
		} else {
			curl_setopt($session, CURLOPT_CUSTOMREQUEST, "GET");
		}

		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

		$result =  curl_exec($session);
		if (! $result){
			$lastError = curl_error($session);
			throw new Exception("Erreur : $lastError");
		}

		$codeResponse = curl_getinfo($session, CURLINFO_HTTP_CODE);

		if ( ! in_array($codeResponse, array('200','201')) ){
			$lastError = curl_error($session);
			if (! $lastError){
				throw new Exception("Erreur $codeResponse (la GED a retourné : $result)");
			}
		}

		return $result;
	}


	public function getRepositoryInfo($url){
		$xmldata = $this->get($url);
		if (! $xmldata){
			return false;
		}

		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($xmldata);

		if (! $xml->getName()){
			$errors = libxml_get_errors();
			throw new Exception("Erreur XML : ".$errors[0]->message);
		}
		/**	@var $repInfo SimpleXMLElement*/
		$repInfo = $xml->children(self::NS_APP)->workspace->children(self::NS_CMIS_RA)->repositoryInfo;

		$result = array();
		foreach($this->getRepositoryRetrieveInfo() as $infoName){
			$result[$infoName] = strval($repInfo->children(self::NS_CMIS)->$infoName);
		}

		$uriTemplate = $xml->children(self::NS_APP)->workspace->children(self::NS_CMIS_RA)->uritemplate;


		foreach ($uriTemplate as $template){
			/**	@var $template SimpleXMLElement*/
			$type = strval($template->children(self::NS_CMIS_RA)->type);
			$result['template'][$type] = strval($template->children(self::NS_CMIS_RA)->template);
		}

		return $result;
	}

	public function getObjectByPath($url,$path) {
		$repositoryInfo = $this->getRepositoryInfo($url);

		if (! $repositoryInfo){
			return false;
		}

		$url_template = $repositoryInfo['template']['objectbypath'];



		$path = urlencode($path);

		$url = str_replace("{path}", $path, $url_template);


		$url = preg_replace("/{[a-zA-Z0-9_]+}/", "", $url);


		$xmldata = $this->get($url);

		if (! $xmldata){
			return false;
		}

		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($xmldata);
		if (! $xml->getName()){
			$errors = libxml_get_errors();
			$this->lastError = "Erreur XML : ".$errors[0]->message;
			return false;
		}

		$result = array('author' => strval($xml->children(self::NS_ATOM)->author->name));
		foreach($this->getFolderRetrieveInfo() as $infoName){
			$result[$infoName] = strval($xml->children(self::NS_ATOM)->$infoName);
		}

		foreach($xml->children(self::NS_ATOM)->link as $link){
			foreach($link->attributes() as $key => $value){
				$attr[$key] = strval($value);
			}

			if (! isset($result['link'][$attr['rel']])){
				$result['link'][$attr['rel']] = $attr['href'];
			}
		}

		return $result;
	}

	private function getUgglyObjectByPath($url,$folder){
		try {
			$folderInfo = $this->getObjectByPath($url, $folder);
		} catch (Exception $e){

			//WTF : Le path est en ISO-8859-1 en Alfresco 4.x et en UTF8 en Alfresco 5.
			// J'ai pas trouvé le truc qui permettait de spécifier l'encodage du bordel.

			$folderInfo = $this->getObjectByPath($url, utf8_decode($folder));
		}
		return $folderInfo;
	}

	public function addDocument($url,$title,$description,$contentType,$content,$gedFolder){
		$folderInfo = $this->getUgglyObjectByPath($url, $gedFolder);

		$url = $folderInfo['link']['down'];
		$content = $this->getContent($title,$description,$contentType,$content);
		$ret = $this->get($url, $content);
		return $ret;
	}

	public function createFolder($url,$folder,$title,$description){
		$folderInfo = $this->getUgglyObjectByPath($url, $folder);

		$url = $folderInfo['link']['down'];

		$content = $this->getFolder($title,$description);

		$ret = $this->get($url, $content);
		return $ret;
	}

	private function getContent($title,$description,$contentType,$content) {
		ob_start();
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		?>
		<atom:entry xmlns:cmis="http://docs.oasis-open.org/ns/cmis/core/200908/"
					xmlns:cmism="http://docs.oasis-open.org/ns/cmis/messaging/200908/"
					xmlns:atom="http://www.w3.org/2005/Atom"
					xmlns:app="http://www.w3.org/2007/app"
					xmlns:cmisra="http://docs.oasis-open.org/ns/cmis/restatom/200908/">
			<atom:title><?php echo $title ?></atom:title>
			<atom:summary><?php echo $description ?></atom:summary>
			<cmisra:content>
				<cmisra:mediatype><?php echo  $contentType ?></cmisra:mediatype>
				<cmisra:base64>
					<?php echo base64_encode($content);?>
				</cmisra:base64>
			</cmisra:content>
			<cmisra:object>
				<cmis:properties>
					<cmis:propertyId propertyDefinitionId="cmis:objectTypeId">
						<cmis:value>cmis:document</cmis:value>
					</cmis:propertyId>
				</cmis:properties>
			</cmisra:object>
		</atom:entry>
		<?php
		return ob_get_clean();
	}

	private function getFolder($title,$description) {
		ob_start();
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		?>
		<atom:entry xmlns:cmis="http://docs.oasis-open.org/ns/cmis/core/200908/"
					xmlns:cmism="http://docs.oasis-open.org/ns/cmis/messaging/200908/"
					xmlns:atom="http://www.w3.org/2005/Atom"
					xmlns:app="http://www.w3.org/2007/app"
					xmlns:cmisra="http://docs.oasis-open.org/ns/cmis/restatom/200908/">
			<atom:title><?php echo $title ?></atom:title>
			<atom:summary><?php echo $description ?></atom:summary>

			<cmisra:object>
				<cmis:properties>
					<cmis:propertyId propertyDefinitionId="cmis:objectTypeId">
						<cmis:value>cmis:folder</cmis:value>
					</cmis:propertyId>
				</cmis:properties>
			</cmisra:object>
		</atom:entry>
		<?php
		return ob_get_clean();
	}
}