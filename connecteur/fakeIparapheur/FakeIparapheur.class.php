<?php
class FakeIparapheur extends Connecteur {
	
	public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties){
		
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
		return $date . " : [Archive] Dossier sign� (simulation de parapheur)!";
	}
	
	public function getSignature($dossierID){
		$info['signature'] = "Signature simul�";
		$info['document'] = "Document";
		$info['nom_document'] = "document.txt";
		return $info;
	}
}