<?php

class PastellCoreActionLongue extends ActionExecutor
{
    public function go()
    {
        sleep(10);
        $this->setLastMessage("L'action longue a été executée");
        return true;
    }
}
