<?php

/**
 * @deprecated Since 4.0.3, Use IparapheurSousType
 */

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

            $circuitPossible = $this->getSousType();
            if (!in_array($fast_parapheur_circuit, $circuitPossible, true)) {
                throw new UnrecoverableException(
                    "Le circuit \"$fast_parapheur_circuit\" n'existe pas ou est mal orthographié"
                );
            }

            $donneesFormulaire->setData('fast_parapheur_circuit', $fast_parapheur_circuit);
        } else {
            $sous_type_iparapheur = $recuperateur->get('iparapheur_sous_type');

            $sousTypePossible = $this->getSousType() ? : [];
            if (!in_array($sous_type_iparapheur, $sousTypePossible, true)) {
                throw new UnrecoverableException(
                    "Le sous-type \"$sous_type_iparapheur\" n'existe pas pour le type configuré"
                );
            }

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
                'connector/fastParapheur/FastParapheurCircuit'
            );
        } else {
            $this->setViewParameter('sous_type', $this->getSousType() ? : []);
            $this->renderPage(
                "Choix d'un type de dossier",
                'connector/iparapheur/IparapheurSousType'
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
