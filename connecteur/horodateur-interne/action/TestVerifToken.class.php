<?php

class TestVerifToken extends ActionExecutor
{
    public function go()
    {
        /** @var HorodateurPastell $horodateur */
        $horodateur = $this->getMyConnecteur();
        $data = "Ceci est un token en utf-8 école" . mt_rand(0, mt_getrandmax());
        $token = $horodateur->getTimestampReply($data);
        $horodateur->verify($data, $token);
        $this->setLastMessage("Vérification: OK");
        return true;
    }
}
