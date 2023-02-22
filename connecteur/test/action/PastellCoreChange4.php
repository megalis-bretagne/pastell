<?php

class PastellCoreChange4 extends ActionExecutor
{
    public function go()
    {
        $df = $this->getConnecteurProperties();

        //getMyConnecteur()->getDocDonneesFormulaire();
        $champs3 = $df->get('champs3');
        $df->setData('champs4', $champs3);
        return true;
    }
}
