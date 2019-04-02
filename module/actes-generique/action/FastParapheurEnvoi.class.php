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

        $result = $signature->sendDocument(
            $filename,
            $acte->get('fast_parapheur_circuit'),
            '',
            $file_content,
            '',
            []);

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