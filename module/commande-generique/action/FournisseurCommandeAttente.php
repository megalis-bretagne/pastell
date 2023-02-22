<?php

class FournisseurCommandeAttente extends ActionExecutor
{
    public function go()
    {

        $last_action = $this->getDocumentActionEntite()->getLastAction($this->id_e, $this->id_d);

        $message = "";
        if ($last_action == 'envoi-mail') {
            $message .= "Attente de réception";
        }

        $this->setLastMessage($message);
        return true;
    }
}
