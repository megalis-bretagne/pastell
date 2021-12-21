<?php

class TestToken extends ActionExecutor
{
    public function go()
    {
        /** @var HorodateurPastell $horodateur */
        $horodateur = $this->getMyConnecteur();
        $data = "Ceci est un token en utf-8 école" . mt_rand(0, mt_getrandmax());
        $token = $horodateur->getTimestampReply($data);
        $token_text = $this->objectInstancier->OpensslTSWrapper->getTimestampReplyString($token);
        if (!$token_text) {
            throw new Exception("Le token de retour est vide");
        }
        $this->setLastMessage("Connexion OpenSign OK: <br/><br/>" . nl2br($token_text));
        return true;
    }
}
