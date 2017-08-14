<?php
class AsalaeREST extends SAEConnecteur {
	
	private $curlWrapper;
	private $tmpFile;
	
	private $url;
	private $login;
	private $password;
	private $originatingAgency;
	
	private $last_error_code;

	/** @var  DonneesFormulaire */
	private $connecteur_config;
	
	public function __construct(CurlWrapper $curlWrapper, TmpFile $tmpFile){
		$this->curlWrapper = $curlWrapper;
		$this->tmpFile = $tmpFile; 
	}
	
	public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire) {
		$this->url = $donneesFormulaire->get('url');
		$this->login = $donneesFormulaire->get('login');
		$this->password = $donneesFormulaire->get('password');
		$this->originatingAgency = $donneesFormulaire->get('originating_agency');
		$this->connecteur_config = $donneesFormulaire;
	}
	
	public function sendArchive($bordereauSEDA,$archivePath,$file_type="TARGZ",$archive_file_name="archive.tar.gz") {
		$bordereau_file = $this->tmpFile->create();	
		file_put_contents($bordereau_file, $bordereauSEDA);
		
		$this->curlWrapper->addPostFile('seda_message', $bordereau_file,"bordereau.xml");
		$this->curlWrapper->addPostFile('attachments', $archivePath,$archive_file_name);
		try {
            $this->getWS('/sedaMessages');
        } catch (Exception $e){
            $this->tmpFile->delete($bordereau_file);
            throw $e;
        }

        $this->tmpFile->delete($bordereau_file);
        
		return true;
	}
	
	public function getLastErrorCode(){
		return $this->last_error_code;
	}
	
	public function getErrorString($number){
		return "Erreur non identifié";
	}
	
	public function getAcuseReception($id_transfert) {
		$org = $this->originatingAgency;
		$result = $this->getWS("/sedaMessages/sequence:ArchiveTransfer/message:Acknowledgement/originOrganizationIdentification:$org/originMessageIdentifier:$id_transfert","application/xml");
		if (!$result){
			$this->last_error_code = 7;
			return false;
		}
		return $result;
	}
	
	public function getReply($id_transfert) {
		$org = $this->originatingAgency;
		$result = $this->getWS("/sedaMessages/sequence:ArchiveTransfer/message:ArchiveTransferReply/originOrganizationIdentification:$org/originMessageIdentifier:$id_transfert","application/xml");
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
	
	private function getWS($url,$accept = "application/json"){
		$this->curlWrapper->httpAuthentication($this->login, hash("sha256",$this->password));

		//see : http://stackoverflow.com/a/19250636
		$this->curlWrapper->addHeader("Expect","");
		$this->curlWrapper->addHeader("Accept",$accept);

        $this->curlWrapper->dontVerifySSLCACert();
		$result = $this->curlWrapper->get($this->url.$url);
		if (! $result){
			throw new Exception($this->curlWrapper->getLastError());
		}
		$http_code = $this->curlWrapper->getHTTPCode();
		if ($http_code != 200){
			throw new Exception("$result - code d'erreur HTTP : $http_code");
		}
        $old_result = $result;
		if ($accept == "application/json"){
			$result = json_decode($result,true);
		}
		if (! $result){
			throw new Exception("Le serveur As@lae n'a pas renvoyé une réponse compréhensible - problème de configuration ? : $old_result");
		}

		return $result;
	}
	
	public function getVersion(){
		return $this->getWS('/versions');
	}
	
	public function ping(){
		return $this->getWS('/ping');
	}
	
}