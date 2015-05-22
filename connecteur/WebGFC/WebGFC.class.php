<?php
class WebGFC extends Connecteur {
	

	public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
		$this->wsdl = $donneesFormulaire->get('wsdl');
		$this->login  = $donneesFormulaire->get('login');
		$this->password  = $donneesFormulaire->get('password');
	}
	
	private $lastMessage;
	
	private function getSoapClient(){
		return new SoapClient($this->wsdl,array('login' => $this->login, 'password' => $this->password));
	}
	
	public function getLastMessage(){
		return $this->lastMessage;
	}
	
	public function echoTest($string){
		$ws = $this->getSoapClient();
		return $ws->echotest($string);
	}
    
     public function getGFCCollectivites(){
		$ws = $this->getSoapClient();
		$col =  $ws->getGFCCollectivites();
        foreach($col as $type){
			$result[$type->anyType[0]] = $type->anyType[1];
		}
        return $result;
    }
	
	public function getTypes($collectiviteId){
		$ws = $this->getSoapClient();
		$data =  $ws->getGFCTypes($collectiviteId);
		foreach($data as $type){
			$result[$type->anyType[0]] = $type->anyType[1];
		}
		return $result;
	}
	
	public function getSousTypes($siren,$type_nom){
		$ws = $this->getSoapClient();
		$data = $ws->getGFCSoustypes($siren,$type_nom);
		$result = array();
		foreach($data as $type){
			$result[$type->anyType[0]] = $type->anyType[1];
		}
		return $result;
	}

	public function createCourrier($collId,$messageSousTypeId,$contact,$titre,$object,$fichier,$username){
		$ws = $this->getSoapClient();
		$response = $ws->createCourrier($collId,$messageSousTypeId,$contact,utf8_encode($titre),utf8_encode($object),$fichier,$username);

		if ($response->CourrierId != -1){
			$this->lastMessage = $response->CodeRetour . " " . utf8_decode($response->Message);
			return $response->CourrierId;
		}
		$this->lastMessage = $response->CodeRetour . " " .utf8_decode($response->Message); 
		return false; //FIXME Etrange
	}
	
	public function getStatus($collId, $courrierID){
		$ws = $this->getSoapClient();
		$response = $ws->getStatut($collId, $courrierID);

		$this->lastMessage = $response->CodeRetour . " " .utf8_decode($response->Message);
		
		return $response->CodeRetour;
	}
	
	public function setInfo($type_num,$type_message){
		return json_encode(array($type_num,$type_message));
	}
	
	public function getInfo($raw_type_message){
		return json_decode($raw_type_message);
	}
	
}