<?php

class MailsecTest extends ActionExecutor
{
    public function go()
    {
        /** @var MailSec $mailsec */
        $mailsec = $this->getMyConnecteur();
        $to  = $mailsec->test();
        $this->setLastMessage("Un email a été envoyé à $to");
        return true;
    }
}
