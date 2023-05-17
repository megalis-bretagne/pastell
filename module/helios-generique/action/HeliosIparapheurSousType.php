<?php

class HeliosIparapheurSousType extends ChoiceActionExecutor
{
    /**
     * @throws Exception
     */
    public function go()
    {
        $recuperateur = $this->getRecuperateur();

        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');
        $donneesFormulaire = $this->getDonneesFormulaire();

        if ($signature->isFastSignature()) {
            $fast_parapheur_circuit = $recuperateur->get('fast_parapheur_circuit');
            $donneesFormulaire->setData('fast_parapheur_circuit', $fast_parapheur_circuit);
        } else {
            $sous_type_iparapheur = $recuperateur->get('iparapheur_sous_type');
            $signature_config = $this->getConnecteurConfigByType('signature');
            $type_iparapheur = $signature_config->get('iparapheur_type');

            $donneesFormulaire->setData('iparapheur_type', $type_iparapheur);
            $donneesFormulaire->setData('iparapheur_sous_type', $sous_type_iparapheur);
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function displayAPI()
    {
        return $this->getSousType();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function display()
    {
        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');

        if ($signature->isFastSignature()) {
            $this->setViewParameter('circuits', $this->getSousType());
            $this->renderPage(
                "Choix d'un type de dossier",
                __DIR__ . "/../../../connecteur/fast-parapheur/templates/FastParapheurCircuit.php"
            );
        } else {
            $this->setViewParameter('sous_type', $this->getSousType() ? : []);
            $this->renderPage(
                "Choix d'un type de dossier",
                __DIR__ . "/../../../connecteur/iParapheur/template/IparapheurSousType.php"
            );
        }
        return true;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function getSousType()
    {
        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');
        return $signature->getSousType();
    }
}
