<?php

class FactureCPPIparapheurSousType extends ChoiceActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $recuperateur = $this->getRecuperateur();
        $sous_type_iparapheur = $recuperateur->get('iparapheur_sous_type');

        $signature_config = $this->getConnecteurConfigByType('signature');
        $type_iparapheur = $signature_config->get('iparapheur_type');

        $donneesFormulaire = $this->getDonneesFormulaire();
        $donneesFormulaire->setData('iparapheur_type', $type_iparapheur);
        $donneesFormulaire->setData('iparapheur_sous_type', $sous_type_iparapheur);

        return true;
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
     * @throws NotFoundException
     * @throws Exception
     */
    public function display()
    {
        $this->setViewParameter('sous_type', $this->getSousType() ? : []);
        $this->renderPage("Choix d'un type de dossier", 'connector/iparapheur/IparapheurSousType');
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
