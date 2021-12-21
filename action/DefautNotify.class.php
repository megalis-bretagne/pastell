<?php

class DefautNotify extends ActionExecutor
{
    public function go()
    {
        $actionName  = $this->getActionName();
        $message = "L'action $actionName a été executée sur le document";
        $this->addActionOK($message);
        $this->notify($actionName, $this->type, $message);
        return true;
    }
}
