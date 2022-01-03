<?php

/** @deprecated */
class PDFGeneriqueEnvoieIParapheur extends ActionExecutor
{
    public function go()
    {

        /** @var IParapheur $signature */
        $signature = $this->getConnecteur('signature');

        $donneesFormulaire = $this->getDonneesFormulaire();

        $file_content = $donneesFormulaire->getFileContent('document');
        $content_type = $donneesFormulaire->getContentType('document');

        $finfo = new finfo(FILEINFO_MIME);

        $annexe = array();
        if ($donneesFormulaire->get('annexe')) {
            foreach ($donneesFormulaire->get('annexe') as $num => $fileName) {
                $annexe_content =  file_get_contents($donneesFormulaire->getFilePath('annexe', $num));
                $annexe_content_type = $finfo->file($donneesFormulaire->getFilePath('annexe', $num), FILEINFO_MIME_TYPE);

                $annexe[] = array(
                    'name' => $fileName,
                    'file_content' => $annexe_content,
                    'content_type' => $annexe_content_type,
                );
            }
        }

        if ($donneesFormulaire->get('has_date_limite')) {
            $date_limite = $donneesFormulaire->get('date_limite');
        } else {
            $date_limite = false;
        }

        $dossierTitre = $donneesFormulaire->get("libelle");

        $metadata = $donneesFormulaire->getFileContent("json_metadata");
        $metadata = json_decode($metadata, true);

        $dossierID = date("YmdHis") . mt_rand(0, mt_getrandmax());
        $donneesFormulaire->setData('iparapheur_dossier_id', $dossierID);
        $signature->setSendingMetadata($donneesFormulaire);

        $result = $signature->sendDocument(
            $donneesFormulaire->get('iparapheur_type'),
            $donneesFormulaire->get('iparapheur_sous_type'),
            $dossierID,
            $file_content,
            $content_type,
            $annexe,
            $date_limite,
            "",
            false,
            "",
            "",
            "",
            $metadata,
            $dossierTitre
        );
        if (! $result) {
            $this->setLastMessage("La connexion avec le iParapheur a échoué : " . $signature->getLastError());
            return false;
        }

        $this->addActionOK("Le document a été envoyé au parapheur électronique");
        $this->notify($this->action, $this->type, "Le document a été envoyé au parapheur électronique");

        return true;
    }
}
