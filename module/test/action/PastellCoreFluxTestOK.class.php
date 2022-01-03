<?php

class PastellCoreFluxTestOK extends ActionExecutor
{
    public function go()
    {
        $this->setLastMessage("OK !");
        return true;
    }
}
