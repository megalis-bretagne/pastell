<?php

class CppModifStatutDemande extends ActionExecutor
{
    public function go()
    {
        $doc = $this->getDonneesFormulaire();
        $statut_cible_liste = $doc->get('statut_cible_liste');
        $this->addActionOK("Demande de modification en statut cible " . $statut_cible_liste);
        return true;
    }
}
