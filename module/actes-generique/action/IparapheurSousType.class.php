<?php

class IparapheurSousType extends ChoiceActionExecutor
{
    /**
     * @throws Exception
     */
    public function go()
    {
        $recuperateur = $this->getRecuperateur();

        $stringMapping = $this->getIdMapping();

        $iparapheur_sous_type_element = $stringMapping->get('iparapheur_sous_type');
        $iparapheur_type_element = $stringMapping->get('iparapheur_type');
        $fast_parapheur_circuit_element = $stringMapping->get('fast_parapheur_circuit');
        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');

        if ($signature->isFastSignature()) {
            $fast_parapheur_circuit = $recuperateur->get('fast_parapheur_circuit');

            $donneesFormulaire = $this->getDonneesFormulaire();
            $donneesFormulaire->setData($fast_parapheur_circuit_element, $fast_parapheur_circuit);
        } else {
            $sous_type_iparapheur = $recuperateur->get('iparapheur_sous_type');
            $signature_config = $this->getConnecteurConfigByType('signature');
            $type_iparapheur = $signature_config->get('iparapheur_type');

            $donneesFormulaire = $this->getDonneesFormulaire();
            $donneesFormulaire->setData($iparapheur_type_element, $type_iparapheur);
            $donneesFormulaire->setData($iparapheur_sous_type_element, $sous_type_iparapheur);
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
            $this->setViewParameter('sous_type', $this->getSousType());
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

    /**
     * @return array
     * @throws Exception
     */
    public function displayChoiceForSearch()
    {
        try {
            $config = $this->getConnecteurConfigByType('signature');
            /** @var SignatureConnecteur $signature */
            $signature = $this->getConnecteur('signature');
            $result = [];
        } catch (Exception $e) {
            /** Aucun connecteur configuré */
            return [];
        }
        $data = $signature->isFastSignature()
            ? explode("\n", $config->getFileContent('fast_parapheur_circuit'))
            : explode("\n", $config->getFileContent('iparapheur_sous_type'));
        foreach ($data as $key => $name) {
            if ($name) {
                $result[$name] = $name;
            }
        }
        return $result;
    }
}
