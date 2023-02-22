<?php

class TdtSendReponsePref extends ConnecteurTypeActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var TdtConnecteur $tdT */
        $tdT = $this->getConnecteur("TdT");
        $tdT->sendResponse($this->getDonneesFormulaire());
        $message = "Réponse envoyée à la préfecture";
        $this->addActionOK($message);
        $this->setLastMessage($message);

        return true;
    }
}
