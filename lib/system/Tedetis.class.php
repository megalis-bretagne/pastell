<?php

require_once( PASTELL_PATH . "/lib/formulaire/DonneesFormulaire.class.php");

class Tedetis {
	
	const URL_TEST = "/modules/actes/";
	const URL_CLASSIFICATION = "/modules/actes/actes_classification_fetch.php";
	const URL_POST_ACTES =  "/modules/actes/actes_transac_create.php";
	
	private $isActivate;
	private $tedetisURL;
	
	private $curlHandle;
	private $lastError;
	private $postData;
	
	public function __construct(DonneesFormulaire $collectiviteProperties){
		$this->isActivate = $collectiviteProperties->get('tdt_activate');
		$this->tedetisURL = $collectiviteProperties->get('tdt_url');
		
		$this->curlHandle = curl_init();
		
		$this->setCurlProperties( CURLOPT_RETURNTRANSFER , 1); 
		$this->setCurlProperties( CURLOPT_CAINFO , $collectiviteProperties->getFilePath('tdt_server_certificate')); 
		$this->setCurlProperties( CURLOPT_SSL_VERIFYHOST , 0 ); 
		
		$this->setCurlProperties( CURLOPT_SSLCERT, $collectiviteProperties->getFilePath('tdt_user_certificat'));
		$this->setCurlProperties( CURLOPT_SSLKEY, $collectiviteProperties->getFilePath('tdt_user_key'));
		$this->setCurlProperties( CURLOPT_SSLKEYPASSWD, $collectiviteProperties->get('tdt_user_key_password'));
		
		$this->postData = array();
	}
	
	private function setCurlProperties($properties,$values){
		curl_setopt($this->curlHandle, $properties, $values); 
	}
	
	public function __destruct(){
		curl_close($this->curlHandle);
	}
	
	public function setUserCertificate($cert_file,$key_file,$key_file_password){	
	
	}

	public function getLastError(){
		return $this->lastError;
	}
	
	private function exec($url){
		if (! $this->isActivate){
			$this->lastError = "Ce module n'est pas activ�";
			return false;
		}
		
		$this->setCurlProperties(CURLOPT_URL,$this->tedetisURL . $url);
		
		if ($this->postData){
				$this->setCurlProperties(CURLOPT_POST,true);
				$this->setCurlProperties(CURLOPT_POSTFIELDS,$this->postData);
		}
		
		$output = curl_exec($this->curlHandle);
		
		$this->lastError = curl_error($this->curlHandle);
		if ($this->lastError){
			$this->lastError = "Erreur de connexion au T�detis : " . $this->lastError;
			return false;
		}	
		
		return $output;
	}
	
	public function testConnexion(){
		return $this->exec(self::URL_TEST);
	}
	
	public function getClassification(){
		return $this->exec( self::URL_CLASSIFICATION );
	}
	
	public function postActes(DonneesFormulaire $donneesFormulaire) {
		
		$nature = 3;
		$dataClassif = array('1','2','2');	
		
		$this->postData['api'] = 1;
		$this->postData['nature_code'] = $nature;
		$this->postData['number'] = $donneesFormulaire->get('numero_de_lacte');
		$this->postData['subject'] =$donneesFormulaire->get('objet');
		$this->postData['decision_date'] = $donneesFormulaire->get('date_de_lacte');
		
		$file_path = $donneesFormulaire->getFilePath('arrete');
		$file_name = $donneesFormulaire->get('arrete');
		$this->postData['acte_pdf_file'] = "@$file_path;filename=$file_name" ;
		
		foreach($dataClassif as $i => $elementClassif){
			$this->postData['classif' . ( $i + 1)] = $elementClassif;  
		}
		
		$result = $this->exec( self::URL_POST_ACTES );	
		if( ! $result ){
			return false;
		}	
				
		if (! preg_match("/^OK/",$result)){
			$this->lastError = "Erreur lors de la transmission, T�d�tis a r�pondu : $result";
			return false;
		}
		return true;		
	}
}