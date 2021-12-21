<?php

class StandardAction extends ActionExecutor
{
    /**
     * @return mixed
     * @throws RecoverableException
     */
    public function go()
    {
        $connecteurTypeActionExecutor = $this->getConnecteurTypeActionExecutor();
        $result = $connecteurTypeActionExecutor->go();
        $this->setLastMessage($connecteurTypeActionExecutor->getLastMessage());
        return $result;
    }
}
