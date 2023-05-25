<?php

class PastellCoreTestExternalData extends ChoiceActionExecutor
{
    public function go()
    {

        $recuperateur = $this->getRecuperateur();
        $choix = $recuperateur->get('choix');

        $donneesFormulaire = $this->getDonneesFormulaire();
        $donneesFormulaire->setData('test_external_data', $choix);

        return true;
    }

    /**
     * @throws NotFoundException
     */
    public function display()
    {
        $this->renderPage('Choix', 'module/test/TestChoix');
        return true;
    }

    public function displayAPI()
    {
        return ['pierre','feuille','ciseaux','lézard','Spock'];
    }
}
