<?php

class IParapheurEnvoie extends ActionExecutor
{

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
    private function goIparapheur()
    {
        /** @var IParapheur $signature */
        $signature = $this->getConnecteur('signature');

        $actes = $this->getDonneesFormulaire();

        $file_content = file_get_contents($actes->getFilePath('arrete'));
        $finfo = new finfo(FILEINFO_MIME);
        $content_type = $finfo->file($actes->getFilePath('arrete'), FILEINFO_MIME_TYPE);

        $annexe = array();
        if ($actes->get('autre_document_attache')) {
            foreach ($actes->get('autre_document_attache') as $num => $fileName) {
                $annexe_content = file_get_contents($actes->getFilePath('autre_document_attache', $num));
                $annexe_content_type = $finfo->file($actes->getFilePath('autre_document_attache', $num), FILEINFO_MIME_TYPE);

                $annexe[] = array(
                    'name' => $fileName,
                    'file_content' => $annexe_content,
                    'content_type' => $annexe_content_type,
                );

            }
        }
        $signature->setSendingMetadata($actes);
        $dossierID = $signature->getDossierID($actes->get('numero_de_lacte'), $actes->get('objet'));
        $result = $signature->sendDocument($actes->get('iparapheur_type'),
            $actes->get('iparapheur_sous_type'),
            $dossierID,
            $file_content,
            $content_type,
            $annexe);
        if (!$result) {
            $this->setLastMessage("La connexion avec le iParapheur a échoué : " . $signature->getLastError());
            return false;
        }

        $this->addActionOK("Le document a été envoyé au parapheur électronique");
        $this->notify($this->action, $this->type, "Le document a été envoyé au parapheur électronique");

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

        $acte = $this->getDonneesFormulaire();

        $filename = $acte->get('arrete')[0];
        $file_path = $acte->getFilePath('arrete');

        $file_content = file_get_contents($file_path);

        $annexe = [];

        $annexe_field_key = 'autre_document_attache';
        if ($acte->get($annexe_field_key)) {
            foreach ($acte->get($annexe_field_key) as $num => $fileName) {
                $filePath = $acte->getFilePath($annexe_field_key, $num);
                $annexe_content = file_get_contents($filePath);

                $annexe[] = [
                    'name' => $fileName,
                    'file_content' => $annexe_content,
                    'file_path' => $filePath
                ];
            }
        }

        $result = $signature->sendDocument(
            $filename,
            $acte->get('fast_parapheur_circuit'),
            $file_path,
            $file_content,
            '',
            $annexe);

        if (!$result) {
            $this->setLastMessage("La connexion avec le parapheur a échouée : " . $signature->getLastError());
            return false;
        }
        $acte->setData('iparapheur_dossier_id', $result);

        $this->addActionOK("Le document a été envoyé au parapheur électronique");
        $this->notify($this->action, $this->type, "Le document a été envoyé au parapheur électronique");

        return true;
    }

}