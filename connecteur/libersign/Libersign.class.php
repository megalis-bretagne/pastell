<?php
class Libersign extends SignatureConnecteur {
	
	
	private $collectiviteProperties;
	
	public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties){
		$this->collectiviteProperties = $collectiviteProperties;
	}
	
	public function getSha1($xml_content){
		$tmp_file = tempnam("/tmp/", "pastell_xml_");
		file_put_contents($tmp_file, $xml_content);
		
		
		$xml_starlet_path = $this->collectiviteProperties->get('libersign_xmlstarlet_path')?:'/usr/bin/xmlstarlet';
		if (! is_executable($xml_starlet_path)){
			throw new Exception("Impossible d'executer le programme xmlstarlet ($xml_starlet_path)");
		}
		
		$c14n_file = tempnam("/tmp/", "pastell_xml_c14n_");
		
		$command = "$xml_starlet_path c14n --without-comments {$tmp_file} > {$c14n_file}";
		
		if (! file_exists($c14n_file)){
			throw new Exception("Impossible de créer le fichier XML canonique $c14n_file");
		}
		
		$result = sha1_file($c14n_file);
		
		unlink($tmp_file);
		unlink($c14n_file);
		
		return $result;
	}
	
	public function getNbJourMaxInConnecteur(){
		throw new Exception("Not implemented");
	}
	
	public function getSousType(){
		throw new Exception("Not implemented");
	}
	
	public function getDossierID($id,$name){
		return "n/a";
	}
	
	public function sendDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,array $all_annexes = array()){
		throw new Exception("Not implemented --");
	}
	
	public function getHistorique($dossierID){
		throw new Exception("Not implemented");
	}
	
	public function getSignature($dossierID){
		throw new Exception("Not implemented");
	}
	
	public function sendHeliosDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,$visuel_pdf){	
		throw new Exception("Not implemented");
	}
	
	public function getAllHistoriqueInfo($dossierID){
		throw new Exception("Not implemented");
	}
	
	public function getLastHistorique($dossierID){
		throw new Exception("Not implemented");		
	}
	
	public function effacerDossierRejete($dossierID){
		throw new Exception("Not implemented");
	}
	
	public function isLocalSignature(){
		return true;
	}

}