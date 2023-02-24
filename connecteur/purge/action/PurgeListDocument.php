<?php

class PurgeListDocument extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var Purge $connecteur */
        $connecteur = $this->getMyConnecteur();
        $document_list = $connecteur->listDocument();

        $message = "Liste des dossiers trouvés : <br/>" ;
        foreach ($document_list as $document) {
            $message .= get_hecho("{$document['id_d']} - {$document['titre']} - {$document['last_action_date']}") . "<br/>";
        }
        $this->setLastMessage($message);
        return true;
    }
}
