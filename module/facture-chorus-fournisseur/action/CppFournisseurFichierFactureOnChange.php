<?php

class CppFournisseurFichierFactureOnChange extends ActionExecutor
{
    public function go()
    {
        $fichier_facture_pdf = $this->getDonneesFormulaire()->getFileName("fichier_facture_pdf");
        $this->getDonneesFormulaire()->setData('facture_id_fournisseur', $fichier_facture_pdf);
        $this->getDocument()->setTitre($this->id_d, $fichier_facture_pdf);
        return true;
    }
}
