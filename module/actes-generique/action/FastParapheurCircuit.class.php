<?php

class FastParapheurCircuit extends ChoiceActionExecutor {

    /**
     * @throws Exception
     */
    public function go() {
        $recuperateur = new Recuperateur($_POST);

        $fast_parapheur_circuit = $recuperateur->get('fast_parapheur_circuit');

        $donneesFormulaire = $this->getDonneesFormulaire();
        $donneesFormulaire->setData('fast_parapheur_circuit', $fast_parapheur_circuit);
    }

    /**
     * @throws Exception
     */
    public function displayAPI() {
        return $this->getCircuit();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function display() {
        $this->{'circuits'} = $this->getCircuit();
        $this->renderPage(
            "Choix d'un type de document",
            __DIR__ . "/../../../connecteur/fast-parapheur/templates/FastParapheurCircuit.php"
        );
        return true;
    }

    /**
     * @throws Exception
     */
    private function getCircuit() {
        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');
        return $signature->getSousType();
    }

    /**
     * @throws Exception
     */
    public function displayChoiceForSearch() {
        $result = array();
        $config = $this->getConnecteurConfigByType('signature');
        $data = explode("\n", $config->getFileContent('fast_parapheur_circuit'));
        foreach ($data as $key => $name) {
            if ($name) {
                $result[$name] = $name;
            }
        }
        return $result;
    }
}