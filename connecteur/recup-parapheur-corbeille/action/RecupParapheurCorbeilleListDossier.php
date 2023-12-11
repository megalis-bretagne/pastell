<?php

declare(strict_types=1);

class RecupParapheurCorbeilleListDossier extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go(): bool
    {
        /** @var RecupParapheurCorbeille $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        $listDossier = $recupParapheur->listDossier();

        $message = "Nombre de dossier : {$listDossier['number']}<br/><ul>";
        foreach ($listDossier['first'] as $dossierId => $dossierName) {
            $message .= "<li>$dossierName ($dossierId)</li>";
        }
        $message .= '</ul>';

        $this->setLastMessage($message);
        return true;
    }
}
