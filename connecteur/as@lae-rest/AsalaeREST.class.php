<?php
class AsalaeREST extends SAEConnecteur {

	private $curlWrapperFactory;
	
	private $url;
	private $login;
	private $password;
	private $originatingAgency;
	private $chunk_size_in_bytes;
	
	private $last_error_code;

	/** @var  DonneesFormulaire */
	private $connecteur_config;
	
	public function __construct(CurlWrapperFactory $curlWrapperFactory){
		$this->curlWrapperFactory = $curlWrapperFactory;
	}

	public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire) {
		$this->url = $donneesFormulaire->get('url');
		$this->login = $donneesFormulaire->get('login');
		$this->password = $donneesFormulaire->get('password');
		$this->originatingAgency = $donneesFormulaire->get('originating_agency');
		$this->chunk_size_in_bytes = $donneesFormulaire->get('chunk_size_in_bytes');
		$this->connecteur_config = $donneesFormulaire;

	}

	/**
	 * @param $bordereauSEDA
	 * @param $archivePath
	 * @param string $file_type
	 * @param string $archive_file_name
	 * @return bool
	 * @throws Exception
	 */
	public function sendArchive($bordereauSEDA,$archivePath,$file_type="TARGZ",$archive_file_name="archive.tar.gz") {

		$tmpFile = new TmpFile();
		$bordereau_file = $tmpFile->create();
		file_put_contents($bordereau_file, $bordereauSEDA);

		try {

			if ($this->chunk_size_in_bytes && filesize($archivePath) > $this->chunk_size_in_bytes) {
				$this->sendArchiveByChunk($bordereau_file,$archivePath);
			} else {
				$this->callSedaMessage($bordereau_file, $archivePath);
			}

        } catch (Exception $e){
            $tmpFile->delete($bordereau_file);
            throw $e;
        }

        $tmpFile->delete($bordereau_file);
        
		return true;
	}


	/**
	 * @param $seda_message_path
	 * @param $attachments_path
	 * @param bool $send_chunked_attachments
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function callSedaMessage($seda_message_path,$attachments_path,$send_chunked_attachments = false){
		$curlWrapper = $this->curlWrapperFactory->getInstance();
		$curlWrapper->addPostFile('seda_message', $seda_message_path,'bordereau.xml');
		if ($attachments_path) {
			$curlWrapper->addPostFile('attachments', $attachments_path, basename($attachments_path));
		}
		if ($send_chunked_attachments){
			$curlWrapper->addPostData('send_chunked_attachments',true);
		}
		return $this->getWS('/sedaMessages',"application/json",$curlWrapper);
	}


	/**
	 * @param $seda_message_path
	 * @param $attachments_path
	 * @throws Exception
	 */
	private function sendArchiveByChunk($seda_message_path,$attachments_path){

		//call seda message
		$seda_message_result = $this->callSedaMessage($seda_message_path, false,true);

		//Découper le fichier en chunk



		//boucle sur les chunk


		throw new Exception("Not implemented");
	}




	public function getLastErrorCode(){
		return $this->last_error_code;
	}
	
	public function getErrorString($number){
		return "Erreur non identifié";
	}

	/**
	 * @param $id_transfert
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function getAcuseReception($id_transfert) {
		$org = $this->originatingAgency;
		$result = $this->getWS(
			"/sedaMessages/sequence:ArchiveTransfer/message:Acknowledgement/originOrganizationIdentification:$org/originMessageIdentifier:$id_transfert",
			"application/xml"
		);
		//WTF : ca ne peut jamais arriver ce truc !
		if (!$result){
			$this->last_error_code = 7;
			return false;
		}
		return $result;
	}

	/**
	 * @param $id_transfert
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function getReply($id_transfert) {
		$org = $this->originatingAgency;
		$result = $this->getWS(
			"/sedaMessages/sequence:ArchiveTransfer/message:ArchiveTransferReply/originOrganizationIdentification:$org/originMessageIdentifier:$id_transfert",
			"application/xml"
		);
		//WTF : ca ne peut jamais arriver ce truc !
		if (!$result){
			$this->last_error_code = 8;
			return false;
		}
		return $result;	
	}
	
	public function getURL($cote) {
		$tab = parse_url($this->url);
		return "{$tab['scheme']}://{$tab['host']}/archives/viewByArchiveIdentifier/$cote";
	}

	/**
	 * @param $url
	 * @param string $accept
	 * @return bool|mixed
	 * @throws Exception
	 */
	private function getWS($url,$accept = "application/json",CurlWrapper $curlWrapper = null){
		if (! $curlWrapper){
			$curlWrapper = $this->curlWrapperFactory->getInstance();
		}
		$curlWrapper->httpAuthentication($this->login, hash("sha256",$this->password));

		//see : http://stackoverflow.com/a/19250636
		$curlWrapper->addHeader("Expect","");
		$curlWrapper->addHeader("Accept",$accept);

        $curlWrapper->dontVerifySSLCACert();
		$result = $curlWrapper->get($this->url.$url);
		if (! $result){
			throw new Exception($curlWrapper->getLastError());
		}
		$http_code = $curlWrapper->getHTTPCode();
		if ($http_code != 200){
			throw new Exception("$result - code d'erreur HTTP : $http_code");
		}
        $old_result = $result;
		if ($accept == "application/json"){
			$result = json_decode($result,true);
		}
		if (! $result){
			throw new Exception(
				"Le serveur As@lae n'a pas renvoyé une réponse compréhensible - problème de configuration ? : $old_result"
			);
		}

		return $result;
	}

	/**
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function getVersion(){
		return $this->getWS('/versions');
	}

	/**
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function ping(){
		return $this->getWS('/ping');
	}
	
}