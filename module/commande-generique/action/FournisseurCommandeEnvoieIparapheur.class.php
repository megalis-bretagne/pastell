<?php

class FournisseurCommandeEnvoieIparapheur extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {

        /** @var IParapheur $signature */
        $signature = $this->getConnecteur('signature');

        $donneesFormulaire = $this->getDonneesFormulaire();

        $signature->setSendingMetadata($donneesFormulaire);


        $finfo = new finfo(FILEINFO_MIME);
        $file_content = $donneesFormulaire->getFileContent('commande');
        $filename_commande = $donneesFormulaire->getFileName('commande');
        $content_type = $finfo->file($donneesFormulaire->getFilePath('commande'), FILEINFO_MIME_TYPE);

        $annexe = array();
        if ($donneesFormulaire->get('autre_document_attache')) {
            foreach ($donneesFormulaire->get('autre_document_attache') as $num => $fileName) {
                $annexe_content =  file_get_contents($donneesFormulaire->getFilePath('autre_document_attache', $num));
                $annexe_content_type = $finfo->file($donneesFormulaire->getFilePath('autre_document_attache', $num), FILEINFO_MIME_TYPE);

                $annexe[] = array(
                        'name' => $fileName,
                        'file_content' => $annexe_content,
                        'content_type' => $annexe_content_type,
                );
            }
        }

        $dossierID = date("YmdHis") . mt_rand(0, mt_getrandmax());
        $donneesFormulaire->setData('iparapheur_dossier_id', $dossierID);

        $date_limite = false;

        $dossierTitre = $donneesFormulaire->get('libelle') . " " . $filename_commande;

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
            "",
            $dossierTitre
        );
        if (! $result) {
            $this->setLastMessage("La connexion avec le iParapheur a échoué : " . $signature->getLastError());
            return false;
        }
        $this->addActionOK("Le document a été envoyé au parapheur électronique");
        return true;
    }
}
