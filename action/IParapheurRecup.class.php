<?php


class IParapheurRecup extends ActionExecutor {
	
	public function go(){
		
		$signature = $this->getConnecteur('signature');

		$actes = $this->getDonneesFormulaire();
		
		$dossierID = $signature->getDossierID($actes->get('numero_de_lacte'),$actes->get('objet'));
		
		$result = $signature->getHistorique($dossierID);				
		if (! $result){
			$this->setLastMessage("La connexion avec le iParapheur a �chou� : " . $signature->getLastError());
			return false;
		}
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
		
		$actes = $this->getDonneesFormulaire();
		$dossierID = $signature->getDossierID($actes->get('numero_de_lacte'),$actes->get('objet'));
		
		$info = $signature->getSignature($dossierID);
		if (! $info ){
			$this->setLastMessage("La signature n'a pas pu �tre r�cup�r� : " . $signature->getLastError());
			return false;
		}
		
		$actes->setData('has_signature',true);
		if ($info['signature']){
			$actes->addFileFromData('signature',"signature.zip",$info['signature']);
		}
		$actes->addFileFromData('document_signe',$info['nom_document'],$info['document']);
		
		$this->setLastMessage("La signature a �t� r�cup�r�e");
		
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'recu-iparapheur',"La signature a �t� r�cup�r�e sur parapheur �lectronique");			
		return true;
		
	} 
	
}