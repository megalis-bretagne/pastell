<?php

class PurgeTest extends ActionExecutor {


    public function go(){
        /** @var Purge $connecteur */
        $connecteur = $this->getMyConnecteur();
        $document_list = $connecteur->listDocument();

        $message = "Liste des documents trouvées : <br/>" ;
        foreach($document_list as $document){
            $message .= get_hecho("{$document['id_d']} - {$document['titre']} - {$document['last_action_date']}") . "<br/>";
        }
        $this->setLastMessage($message);
        return true;
    }

}