<?php

class HeliosRecupPESRetour extends ActionExecutor
{
    public function go()
    {
        /** @var S2low $tdT */
        $tdT = $this->getConnecteur("TdT");

        $id_retour = $this->getDonneesFormulaire()->get('id_retour');

        if (!$id_retour) {
            $this->setLastMessage("Le document ne dispose pas d'identifiant id_retour");
            return false;
        }
        $tdT->getPESRetourLu($this->getDonneesFormulaire());
        $this->addActionOK("Le fichier PES Retour a été importé à nouveau");
        $this->setLastMessage("Le fichier PES Retour a été importé à nouveau");
        return true;
    }
}
