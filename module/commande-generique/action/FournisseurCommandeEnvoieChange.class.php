<?php

class FournisseurCommandeEnvoieChange extends ActionExecutor
{
    public function go()
    {

        if ($this->getDonneesFormulaire()->get('envoi_signature')) {
            $page = $this->getFormulaire()->getTabNumber("Parapheur");
            $this->redirect("/Document/edition?id_d={$this->id_d}&id_e={$this->id_e}&page=$page");
        }
    }
}
