<?php

require_once(__DIR__ . "/../OpenSign.class.php");

class OpenSignTest extends ActionExecutor
{

    public function go()
    {
        /** @var OpenSign $opensign */
        $opensign = $this->getMyConnecteur();
        $result = $opensign->test();
        $this->setLastMessage("Connexion OpenSign OK: $result");
        return true;
    }
}
