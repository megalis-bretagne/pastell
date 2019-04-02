<?php

class FastParapheurEnvoi extends ActionExecutor
{

    /**
     * @throws Exception
     */
    public function go()
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