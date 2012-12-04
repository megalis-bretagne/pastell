<?php

require_once( PASTELL_PATH . "/lib/Array2XML.class.php");

class IParapheurRecupHelios extends ActionExecutor {
	
	public function go(){
		
		if ($this->from_api == false){
			$this->getJournal()->add(Journal::DOCUMENT_ACTION,$this->id_e,$this->id_d,'verif-iparapheur',"V�rification manuelle du retour iparapheur");
		}
		
		$signature = $this->getConnecteur('signature');
		
		
		$helios = $this->getDonneesFormulaire();
		
		$file_array = $helios->get('fichier_pes');
		$filename = $file_array[0];
		
		$dossierID = $signature->getDossierID($helios->get('objet'),$filename);
		
		$all_historique = $signature->getAllHistoriqueInfo($dossierID);
		
		if (! $all_historique){
			$this->setLastMessage("La connexion avec le iParapheur a �chou� : " . $signature->getLastError());
			return false;
		}
		
		$array2XML = new Array2XML();
		$historique_xml = $array2XML->getXML('iparapheur_historique',json_decode(json_encode($all_historique),true));
		
		
		$helios->setData('has_historique',true);
		$helios->addFileFromData('iparapheur_historique',"iparapheur_historique.xml",$historique_xml);
		
		$result = $signature->getLastHistorique($all_historique);
		
		if (strstr($result,"[Archive]")){
			return $this->retrieveDossier();
		}
		if (strstr($result,"[RejetVisa]") || strstr($result,"[RejetSignataire]")){
			$this->rejeteDossier($result);
			$signature->effacerDossierRejete($dossierID);
		}
		$this->setLastMessage($result);
		return true;			
	}
	
	public function rejeteDossier($result){
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'rejet-iparapheur',"Le document a �t� rejet� dans le parapheur : $result");
	}
	
	public function retrieveDossier(){
		$signature = $this->getConnecteur('signature');
		
		$helios = $this->getDonneesFormulaire();
		$file_array = $helios->get('fichier_pes');
		$filename = $file_array[0];
		
		$dossierID = $signature->getDossierID($helios->get('objet'),$filename);
		
		$info = $signature->getSignature($dossierID);
		if (! $info ){
			$this->setLastMessage("La signature n'a pas pu �tre r�cup�r� : " . $signature->getLastError());
			return false;
		}
		
		$helios->setData('has_signature',true);
		if ($info['signature']){
			$helios->addFileFromData('fichier_pes_signe',$filename,$info['signature']);
		} else {
			$fichier_pes_path = $helios->getFilePath('fichier_pes',0);
			$fichier_pes_content = file_get_contents($fichier_pes_path);
			$helios->addFileFromData('fichier_pes_signe',$filename,$fichier_pes_content);
		}
		$helios->addFileFromData('document_signe',$info['nom_document'],$info['document']);
		
		$this->setLastMessage("La signature a �t� r�cup�r�e");
		
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'recu-iparapheur',"La signature a �t� r�cup�r�e sur parapheur �lectronique");			
		return true;
		
	} 
	
}