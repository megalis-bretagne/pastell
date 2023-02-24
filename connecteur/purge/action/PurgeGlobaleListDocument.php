<?php

class PurgeGlobaleListDocument extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var Purge $connecteur */
        $connecteur = $this->getMyConnecteur();
        $document_list = $connecteur->listDocumentGlobal();

        $message = "Liste des dossiers trouvés : <br/>" ;
        foreach ($document_list as $document) {
            $message .= get_hecho("id_e={$document['id_e']} - id_d={$document['id_d']}  - type={$document['last_type']} - date dernière action={$document['last_action_date']}") . "<br/>";
        }
        $this->setLastMessage($message);
        return true;
    }
}
