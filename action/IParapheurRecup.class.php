<?php

require_once( PASTELL_PATH . "/lib/system/IParapheur.class.php");
require_once( PASTELL_PATH . "/lib/action/ActionExecutor.class.php");

class IParapheurRecup extends ActionExecutor {
	
	public function go(){
		$collectiviteProperties = $this->getCollectiviteProperties();
		$iParapheur = new IParapheur($collectiviteProperties);		
		$actes = $this->getDonneesFormulaire();
		
		$num = $actes->get('numero_de_lacte');
		$result = $iParapheur->getHistorique($num);				
		if (! $result){
			$this->setLastMessage("La connexion avec le iParapheur a �chou� : " . $iParapheur->getLastError());
			return false;
		}
		if (strstr($result,"[Archive]")){
			return $this->retrieveDossier();
		}
		if (strstr($result,"[RejetVisa]") || strstr($result,"[RejetSignataire]")){
			$this->rejeteDossier();
		}
		$this->setLastMessage($result);
		return true;			
	}
	
	public function rejeteDossier(){
		$collectiviteProperties = $this->getCollectiviteProperties();
		$iParapheur = new IParapheur($collectiviteProperties);
		$actes = $this->getDonneesFormulaire();
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'rejet-iparapheur',"Le document a �t� rejet� dans le parapheur");
		
	}
	
	public function retrieveDossier(){
		$collectiviteProperties = $this->getCollectiviteProperties();
		$iParapheur = new IParapheur($collectiviteProperties);		
		$actes = $this->getDonneesFormulaire();
		$info = $iParapheur->getSignature($actes->get('numero_de_lacte'));
		if (! $info ){
			$this->setLastMessage("La signature n'a pas pu �tre r�cup�r� : " . $iParapheur->getLastError());
			return false;
		}
		
		$actes->setData('has_signature',true);
		$actes->addFileFromData('signature',"signature.zip",$info['signature']);
		$actes->addFileFromData('document_signe',$info['nom_document'],$info['document']);
		
		$this->setLastMessage("La signature a �t� r�cup�r�e");
		
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'recu-iparapheur',"La signature a �t� r�cup�r�e sur parapheur �lectronique");			
		return true;
		
	} 
	
}