<?php

class OpenSignTestVerifToken extends ActionExecutor
{
    public function go()
    {
        /** @var OpenSign $opensign */
        $opensign = $this->getMyConnecteur();
        $data = mt_rand(0, mt_getrandmax());
        $token = $opensign->getTimestampReply($data);
        $opensign->verify($data, $token);
        $this->setLastMessage("Vérification: OK");
        return true;
    }
}
