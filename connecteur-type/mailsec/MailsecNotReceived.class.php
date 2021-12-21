<?php

final class MailsecNotReceived extends ConnecteurTypeActionExecutor
{
    public function go()
    {
        $documentEmail = $this->objectInstancier->getInstance(DocumentEmail::class);
        $documentEmail->delete($this->id_d);
        $this->addActionOK('Mail défini comme non-reçu.');
        return true;
    }
}
