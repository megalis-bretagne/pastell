<?php

class IParapheurEnvoieHelios extends ActionExecutor {

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');

        return $signature->isFastSignature()
            ? $this->goFast()
            : $this->goIparapheur();
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function goIparapheur() {
	    /** @var SignatureConnecteur $signature */
		$signature = $this->getConnecteur('signature');

		$helios = $this->getDonneesFormulaire();

        $signature->setSendingMetadata($helios);


        $file_content = file_get_contents($helios->getFilePath('fichier_pes'));
		$finfo = new finfo(FILEINFO_MIME);
		$content_type = $finfo->file($helios->getFilePath('fichier_pes'),FILEINFO_MIME_TYPE);

        //Ajout
        if (! $helios->get('visuel_pdf')){
            $donneesFormulaireConnecteur = $this->objectInstancier->ConnecteurFactory->getConnecteurConfigByType($this->id_e,$this->type,'signature');
            if($donneesFormulaireConnecteur) {
                $pdf_default = $donneesFormulaireConnecteur->get("visuel_pdf_default");
                if( $pdf_default ) {
                    $helios->setData('visuel_pdf', $pdf_default);
                    $visuel_pdf  = file_get_contents($donneesFormulaireConnecteur->getFilePath('visuel_pdf_default'));
                    $helios->addFileFromData('visuel_pdf', $pdf_default[0], $visuel_pdf);
                }
                else {
                    throw new Exception("Le visuel PDF est obligatoire pour l'envoi à la signature");
                }
            }
		}
        else {
			$content_type_visuel = $helios->getContentType('visuel_pdf');
			if ($content_type_visuel == "application/pdf") {
				$visuel_pdf = file_get_contents($helios->getFilePath('visuel_pdf'));
			}
			else {
				throw new Exception("Le visuel PDF doit être au format pdf pour l'envoi à la signature");
			}
        }
        // Fin ajout

		$file_array = $helios->get('fichier_pes');
		$filename = $file_array[0];

		$dossierID = $signature->getDossierID($helios->get('objet'),$filename);
		$dossierID = date("YmdHis") . mt_rand(0, mt_getrandmax());


		$result = $signature->sendHeliosDocument($helios->get('iparapheur_type'),
											$helios->get('iparapheur_sous_type'),
											$dossierID,
											$file_content,
											$content_type,$visuel_pdf);
		if (! $result){
			$this->setLastMessage("La connexion avec le iParapheur a échoué : " . $signature->getLastError());
			return false;
		}

        $helios->setData('iparapheur_dossier_id', $dossierID);
        $this->addActionOK("Le document a été envoyé au parapheur électronique");
		$this->notify($this->action, $this->type,"Le document a été envoyé au parapheur électronique");
		return true;			
	}

    /**
     * @return bool
     * @throws Exception
     */
    private function goFast()
    {
        /** @var FastParapheur $signature */
        $signature = $this->getConnecteur('signature');

        $helios = $this->getDonneesFormulaire();

        $filename = $helios->get('fichier_pes')[0];
        $file_path = $helios->getFilePath('fichier_pes');

        $file_content = file_get_contents($file_path);

        $result = $signature->sendHeliosDocument(
            $filename,
            $helios->get('fast_parapheur_circuit'),
            $file_path,
            $file_content,
            '',
            '');

        if (!$result) {
            $this->setLastMessage("La connexion avec le parapheur a échouée : " . $signature->getLastError());
            return false;
        }
        $helios->setData('iparapheur_dossier_id', $result);

        $this->addActionOK("Le document a été envoyé au parapheur électronique");
        $this->notify($this->action, $this->type, "Le document a été envoyé au parapheur électronique");

        return true;
    }

}