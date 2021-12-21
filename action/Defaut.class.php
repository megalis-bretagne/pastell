<?php

class Defaut extends ActionExecutor
{
    public function go()
    {
        $actionName  = $this->getActionName();
        $this->addActionOK("L'action $actionName a été executée sur le document");
        return true;
    }
}
