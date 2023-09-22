<?php

class MailsecTest extends ActionExecutor
{
    public function go()
    {
        /** @var MailSec $mailsec */
        $mailsec = $this->getMyConnecteur();
        $mailsec->setDocDonneesFormulaire($this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire());
        $to = $mailsec->test();
        $this->setLastMessage("Un email a été envoyé à $to");
        return true;
    }
}
