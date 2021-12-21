<?php

class IparapheurType extends ChoiceActionExecutor
{
    public function go()
    {
        $recuperateur = $this->getRecuperateur();
        $type_iparapheur = $recuperateur->get('iparapheur_type');
        $connecteur_properties = $this->getConnecteurProperties();
        $connecteur_properties->setData('iparapheur_type', $type_iparapheur);
        $actionExecutorFactory = $this->objectInstancier->getInstance("ActionExecutorFactory");
        $actionExecutorFactory->executeOnConnecteur($this->id_ce, $this->id_u, 'update-sous-type');
    }

    public function displayAPI()
    {
        return $this->getType();
    }

    public function display()
    {
        $this->{'type_iparapheur'} = $this->getType();
        $this->renderPage("Choix du type i-Parapheur", __DIR__ . "/../template/IparapheurType.php");
        return true;
    }

    private function getType()
    {
        /** @var IParapheur $signature */
        $signature = $this->getMyConnecteur();
        return $signature->getType();
    }
}
