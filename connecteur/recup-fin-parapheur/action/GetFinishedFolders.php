<?php

declare(strict_types=1);

class GetFinishedFolders extends ActionExecutor
{
    public function go()
    {
        /** @var RecupFinParapheur $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        $listDossier = $recupParapheur->getFinishedFolders();

        $message = 'Nombre de dossier : {' . count($listDossier) . '}<br/><ul>';
        foreach ($listDossier as $dossierId => $dossierName) {
            $message .= "<li>$dossierName ($dossierId)</li>";
        }
        $message .= "</ul>";

        $this->setLastMessage($message);
        return true;
    }
}
