<?php

class UpdateAllIparapheur extends ActionExecutor
{
    public function go()
    {
        $all_connecteur = $this->objectInstancier->getInstance(ConnecteurEntiteSQL::class)->getAllById("iParapheur");
        $result = [];
        foreach ($all_connecteur as $connecteur_info) {
            if ($connecteur_info['id_e'] == 0) {
                continue;
            }
            $this->objectInstancier->getInstance(ActionExecutorFactory::class)->executeOnConnecteur(
                $connecteur_info['id_ce'],
                $this->id_u,
                'update-sous-type'
            );
            $result[] = "{$connecteur_info['denomination']} ({$connecteur_info['libelle']}) : " .
                $this->objectInstancier->getInstance(ActionExecutorFactory::class)->getLastMessage();
        }
        $this->setLastMessage("Résultat :<br/>" . implode("<br/>", $result));
        return true;
    }
}
