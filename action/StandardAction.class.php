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

    public function goLot(array $all_id_d)
    {
        $connecteurTypeActionExecutor = $this->getConnecteurTypeActionExecutor();
        $result = $connecteurTypeActionExecutor->goLot($all_id_d);
        $this->setLastMessage($connecteurTypeActionExecutor->getLastMessage());
        return $result;
    }
}
