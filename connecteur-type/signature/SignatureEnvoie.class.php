<?php

class SignatureEnvoie extends ConnecteurTypeActionExecutor {

	public function go(){
		/** @var SignatureConnecteur $signature */
		$signature = $this->getConnecteur('signature');

		$donneesFormulaire = $this->getDonneesFormulaire();

		$document_element = $this->getMappingValue('document');
		$objet_element = $this->getMappingValue('objet');
		$iparapheur_type_element = $this->getMappingValue('iparapheur_type');
		$iparapheur_sous_type_element = $this->getMappingValue('iparapheur_sous_type');
		$has_date_limite = $this->getMappingValue('iparapheur_has_date_limite');
		$iparapheur_date_limite = $this->getMappingValue('iparapheur_date_limite');
		$annexe_element = $this->getMappingValue('autre_document_attache');

        $signature->setSendingMetadata($donneesFormulaire);

		$file_content = $donneesFormulaire->getFileContent($document_element);
		$content_type = $donneesFormulaire->getContentType($document_element);
		$filename = $donneesFormulaire->getFileName($document_element);


		$annexe = array();
		if ($donneesFormulaire->get($annexe_element)) {
			foreach($donneesFormulaire->get($annexe_element) as $num => $fileName ){
				$annexe_content =  $donneesFormulaire->getFileContent($annexe_element,$num);
				$annexe_content_type = $donneesFormulaire->getContentType($annexe_element,$num);

				$annexe[] = array(
					'name' => $fileName,
					'file_content' => $annexe_content,
					'content_type' => $annexe_content_type,
				);
			}
		}

		$dossierID = $signature->getDossierID($donneesFormulaire->get($objet_element),$filename);
		if ($donneesFormulaire->get($has_date_limite)){
			$date_limite = $donneesFormulaire->get($iparapheur_date_limite);
		} else {
			$date_limite = false;
		}

		$result = $signature->sendDocument(
			$donneesFormulaire->get($iparapheur_type_element),
			$donneesFormulaire->get($iparapheur_sous_type_element),
			$dossierID,
			$file_content,
			$content_type,
			$annexe,
			$date_limite
		);
		if (! $result){
			$this->setLastMessage("La connexion avec le parapheur a échoué : " . $signature->getLastError());
			return false;
		}
		$this->addActionOK("Le document a été envoyé au parapheur électronique");
		return true;
	}

}