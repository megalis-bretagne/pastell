<?php

class FactureCPPEnvoiVisaChange extends ActionExecutor
{
    public function go()
    {

        $envoi_visa = $this->getDonneesFormulaire()->get('envoi_visa');
        if (!$envoi_visa) {
            return;
        }

        $recuperateur = new Recuperateur($_POST);
        if ($recuperateur->get('suivant') || $recuperateur->get('precedent')) {
            return;
        }
        // suppression du redirect car les autres onchange ne sont pas exécutés dans le cas du redirect
        /*
        $page = $this->getFormulaire()->getTabNumber($envoi_visa?"Circuit parapheur":"Cheminement");
        $this->redirect("/document/edition.php?id_d={$this->id_d}&id_e={$this->id_e}&page=$page");
        */
    }
}
