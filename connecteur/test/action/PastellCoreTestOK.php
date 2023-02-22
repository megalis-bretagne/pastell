<?php

class PastellCoreTestOK extends ActionExecutor
{
    public function go()
    {
        $this->setLastMessage("OK !");
        $this->getLogger()->debug("test ok !");
        return true;
    }
}
