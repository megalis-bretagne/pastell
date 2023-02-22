<?php

class TestConnexionSurSlow extends ActionExecutor
{
    public function go()
    {
        /** @var S2low $tdt */
        $tdt = $this->getMyConnecteur();
        $url = $tdt->getURLTestNounce();
        header("Location: $url");
        exit_wrapper(0);
    }
}
