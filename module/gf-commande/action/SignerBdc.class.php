<?php 

class SignerBdc extends ActionExecutor {
	
	public function go(){
		
		$signature = $this->getConnecteur('signature');
		
		$actes = $this->getDonneesFormulaire();
		
		$file_content = file_get_contents($actes->getFilePath('bon_de_commande'));
		$finfo = new finfo(FILEINFO_MIME);
		$content_type = $finfo->file($actes->getFilePath('bon_de_commande'),FILEINFO_MIME_TYPE);
		$dossierID = $actes->getFileName('bon_de_commande');
		
		$result = $signature->sendDocument($actes->get('iparapheur_type'),
											$actes->get('iparapheur_sous_type'),
											$dossierID,
											$file_content,
											$content_type);				
		if (! $result){
			$this->setLastMessage("La connexion avec le iParapheur a �chou� : " . $signature->getLastError());
			return false;
		}
		
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,$this->action,"Le document a �t� envoy� au parapheur �lectronique");			
		
		$this->setLastMessage("Le document a �t� envoy� au parapheur �lectronique");
		return true;			
	}
	
}