<?php

class IParapheurEnvoieDocumentASigner extends ActionExecutor {
	
	public function go(){

	    /** @var IParapheur $signature */
		$signature = $this->getConnecteur('signature');
				
		$donneesFormulaire = $this->getDonneesFormulaire();
		
		$file_content = $donneesFormulaire->getFileContent('document');
		$content_type = $donneesFormulaire->getContentType('document');
		$filename = $donneesFormulaire->getFileName('document');

        $autre_doc = array();
        if ($donneesFormulaire->get('autre_document_attache')) {
            foreach($donneesFormulaire->get('autre_document_attache') as $num => $fileName ){
                $autre_doc_content =  $donneesFormulaire->getFileContent('autre_document_attache',$num);
                $autre_doc_content_type = $donneesFormulaire->getContentType('autre_document_attache',$num);

                $autre_doc[] = array(
                    'name' => $fileName,
                    'file_content' => $autre_doc_content,
                    'content_type' => $autre_doc_content_type,
                );
            }
        }

        $dossierID = $signature->getDossierID($donneesFormulaire->get('libelle'),$filename);
		if ($donneesFormulaire->get('has_date_limite')){
			$date_limite = $donneesFormulaire->get('date_limite');
		} else {
			$date_limite = false;
		}
		
		$result = $signature->sendDocument($donneesFormulaire->get('iparapheur_type'),
									$donneesFormulaire->get('iparapheur_sous_type'),
									$dossierID,
									$file_content,
									$content_type,$autre_doc,$date_limite);
		if (! $result){
			$this->setLastMessage("La connexion avec le iParapheur a échoué : " . $signature->getLastError());
			return false;
		}
		$login_http = $signature->getLogin();
		$this->addActionOK("Le document a été envoyé au parapheur électronique via le login $login_http ");
		return true;			
	}
	
}