<?php

class FournisseurCommandeEnvoieIparapheur extends ActionExecutor {

	public function go(){

		$signature = $this->getConnecteur('signature');

		$donneesFormulaire = $this->getDonneesFormulaire();

		$finfo = new finfo(FILEINFO_MIME);
		$file_content = $donneesFormulaire->getFileContent('commande');
		$filename_commande = $donneesFormulaire->getFileName('commande');
		$content_type = $finfo->file($donneesFormulaire->getFilePath('commande'),FILEINFO_MIME_TYPE);

		$annexe = array();
		if ($donneesFormulaire->get('autre_document_attache')) {
			foreach($donneesFormulaire->get('autre_document_attache') as $num => $fileName ){
				$annexe_content =  file_get_contents($donneesFormulaire->getFilePath('autre_document_attache',$num));
				$annexe_content_type = $finfo->file($donneesFormulaire->getFilePath('autre_document_attache',$num),FILEINFO_MIME_TYPE);
					
				$annexe[] = array(
						'name' => $fileName,
						'file_content' => $annexe_content,
						'content_type' => $annexe_content_type,
				);
		
			}
		}
		
		$dossierID = $signature->getDossierID($donneesFormulaire->get('libelle'),$filename_commande);
		$date_limite = false;

		$result = $signature->sendDocument($donneesFormulaire->get('iparapheur_type'),
			$donneesFormulaire->get('iparapheur_sous_type'),
			$dossierID,
			$file_content,
			$content_type,
			$annexe,
			$date_limite);
		if (! $result){
			$this->setLastMessage("La connexion avec le iParapheur a échoué : " . $signature->getLastError());
			return false;
		}
		$this->addActionOK("Le document a été envoyé au parapheur électronique");
		return true;
	}

}