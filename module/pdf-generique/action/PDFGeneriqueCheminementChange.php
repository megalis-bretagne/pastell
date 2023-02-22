<?php

class PDFGeneriqueCheminementChange extends ActionExecutor
{
    public function go()
    {
        $recuperateur = new Recuperateur($_POST);

        // Don't redirect if the user click on suivant or précédent
        if ($recuperateur->get('suivant') || $recuperateur->get('precedent')) {
            return;
        }

        // Don't redirect if it remains only 2 tabs
        if ($this->getDonneesFormulaire()->getNbOnglet() == 2) {
            return;
        }

        // Redirect user to the third tab
        $page = 2;
        $this->redirect("/Document/edition?id_d={$this->id_d}&id_e={$this->id_e}&page=$page");
    }
}
