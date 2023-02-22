<?php

class StandardChoiceAction extends ChoiceActionExecutor
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

    /**
     * @return mixed
     * @throws RecoverableException
     */
    public function display()
    {
        $connecteurTypeActionExecutor = $this->getConnecteurTypeActionExecutor();
        return $connecteurTypeActionExecutor->display();
    }

    /**
     * @return mixed
     * @throws RecoverableException
     */
    public function displayAPI()
    {
        $connecteurTypeActionExecutor = $this->getConnecteurTypeActionExecutor();
        return $connecteurTypeActionExecutor->displayAPI();
    }
}
