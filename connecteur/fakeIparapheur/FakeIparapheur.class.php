<?php
class FakeIparapheur extends Connecteur {
	
	private $retour;
	
	public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties){
		$this->retour = $collectiviteProperties->get('iparapheur_retour');
	}
	
	public function getNbJourMaxInConnecteur(){
		return 30;
	}
	
	public function getSousType(){
		return array("Arr�t� individuel","Arr�t� r�glementaire","Contrat et convention","D�lib�ration");
	}
	
	public function getDossierID($id,$name){
		$name = preg_replace("#[^a-zA-Z0-9_ ]#", "_", $name);
		return "$id $name";
	}
	
	public function sendDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,array $all_annexes = array()){
		return "Dossier d�pos� pour signature";
	}
	
	public function getHistorique($dossierID){
		
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
		$info['signature'] = "";
		$info['document'] = "Document";
		$info['nom_document'] = "document.txt";
		return $info;
	}
	
	public function sendHeliosDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,$visuel_pdf){	
		return true;
	}
	
	public function getAllHistoriqueInfo($dossierID){
		return array("Fake parapheur");
	}
	
	public function getLastHistorique($dossierID){
		return "[Archive]";
	}
	
	public function effacerDossierRejete($dossierID){
		return true;
	}
}