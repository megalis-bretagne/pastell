<?php

class IParapheurTestSend extends ActionExecutor
{
    /**
     * @return bool
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function go()
    {

        /** @var IParapheur $iParapheur */
        $iParapheur = $this->getMyConnecteur();

        $result = $iParapheur->sendDocumentTest();
        if ($result === null) {
            $last_error = $iParapheur->getLastError();
            $this->setLastMessage("$last_error");
            return false;
        }

        $this->setLastMessage($result);
        return true;
    }
}
