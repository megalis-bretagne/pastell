<?php

class PastellCoreConnecteurTestExternalData extends ChoiceActionExecutor
{
    public function go()
    {

        $recuperateur = $this->getRecuperateur();
        $choix = $recuperateur->get('choix');

        $donneesFormulaire = $this->getConnecteurProperties();
        $donneesFormulaire->setData('external_data', $choix);

        return true;
    }

    public function display()
    {
        $this->renderPage("Choix", __DIR__ . "/../template/TestChoix.php");
        return true;
    }

    public function displayAPI()
    {
        return ['pierre','feuille','ciseaux','lézard','Spock'];
    }
}
