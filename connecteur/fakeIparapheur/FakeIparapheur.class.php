<?php
class FakeIparapheur extends SignatureConnecteur {
	
	private $retour;
	private $iparapheur_type;
	private $iparapheur_envoi_status;
	private $iparapheur_temps_reponse;
	
	public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties){
		$this->retour = $collectiviteProperties->get('iparapheur_retour');
		$this->iparapheur_type = $collectiviteProperties->get('iparapheur_type');
		$this->iparapheur_envoi_status = $collectiviteProperties->get('iparapheur_envoi_status');
		$this->iparapheur_temps_reponse = intval($collectiviteProperties->get('iparapheur_temps_reponse'));
	}
	
	public function getNbJourMaxInConnecteur(){
		return 30;
	}
	
	public function getSousType(){
		switch($this->iparapheur_type){
			case 'Actes':
				return array("Arr�t� individuel","Arr�t� r�glementaire","Contrat et convention","D�lib�ration");
			case 'PES':
				return array("BJ","Bordereau depense");
			case 'Document':
				return array("Courrier","Commande","Facture");
		}
		 
	}
	
	public function getDossierID($id,$name){
		$name = preg_replace("#[^a-zA-Z0-9_ ]#", "_", $name);
		return "$id $name";
	}
	
	public function sendDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,array $all_annexes = array()){
		if ($this->iparapheur_envoi_status == 'error'){
			throw new Exception("Erreur d�clench�e par le connecteur fake Iparapheur (iparapheur_envoi_status configur� � 'error')");
		}
		return "Dossier d�pos� pour signature";
	}
	
	public function getHistorique($dossierID){
		sleep($this->iparapheur_temps_reponse);
		$date = date("d/m/Y H:i:s");
		if( $this->retour == 'Archive' ) {
			return $date . " : [Archive] Dossier sign� (simulation de parapheur)!";
		}
		if( $this->retour == 'Rejet' ) {
			return $date . " : [RejetVisa] Dossier rejet� (simulation parapheur)!";
		}
		
		throw new Exception("Erreur provoqu�e par le simulateur du iParapheur");
	}
	
	public function getSignature($dossierID){
		$info['signature'] = "Test Signature";
		$info['document'] = "Document";
		$info['nom_document'] = "document.txt";
		return $info;
	}
	
	public function sendHeliosDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,$visuel_pdf){	
		return true;
	}
	
	public function getAllHistoriqueInfo($dossierID){
		if ($this->retour == 'Erreur'){
			throw new Exception("Erreur provoqu�e par le simulateur du iParapheur");
		}
		return array("Fake parapheur");
	}
	
	public function getLastHistorique($dossierID){
		
		if( $this->retour == 'Archive' ) {
			return "[Archive]";
		}
		return "[RejetVisa]";
		
	}
	
	public function effacerDossierRejete($dossierID){
		return true;
	}
}