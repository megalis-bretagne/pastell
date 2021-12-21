<?php

class PastellCoreTriggerConnecteurTest extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var TestConnecteur $testConnecteur */
        $testConnecteur = $this->getConnecteur("test");
        $champs1 = $testConnecteur->getChamps1();
        $this->setLastMessage("Le champs 1 du connecteur contient $champs1");
        return true;
    }
}
