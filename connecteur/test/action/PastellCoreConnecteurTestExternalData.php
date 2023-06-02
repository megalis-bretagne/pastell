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

    /**
     * @throws NotFoundException
     */
    public function display()
    {
        $this->renderPage('Choix', 'connector/test/TestChoix');
        return true;
    }

    public function displayAPI()
    {
        return ['pierre','feuille','ciseaux','lézard','Spock'];
    }
}
