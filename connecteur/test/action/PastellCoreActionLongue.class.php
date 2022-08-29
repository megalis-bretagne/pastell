<?php

class PastellCoreActionLongue extends ActionExecutor
{
    public function go()
    {
        sleep(40);
        $this->setLastMessage("L'action longue a été executée");
        return true;
    }
}
