<?php

class GlaneurTester extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var GlaneurConnecteur $glaneurLocal */
        $glaneurLocal = $this->getMyConnecteur();

        $message = $glaneurLocal->listDirectories();

        $this->setLastMessage($message);
        return true;
    }
}
