<?php

class RecupParapheurListDossier extends ActionExecutor
{
    public function go()
    {
        /** @var RecupParapheur $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        $listDossier = $recupParapheur->listDossier();

        $message = "Nombre de dossier : {$listDossier['number']}<br/><ul>";
        foreach ($listDossier['first'] as $dossierId => $dossierName) {
            $message .= "<li>$dossierName ($dossierId)</li>";
        }
        $message .= "</ul>";

        $this->setLastMessage($message);
        return true;
    }
}
