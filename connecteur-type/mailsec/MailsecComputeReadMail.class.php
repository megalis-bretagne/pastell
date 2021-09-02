<?php

class MailsecComputeReadMail extends ConnecteurTypeActionExecutor
{
    private const READ_MAIL_NUMBER_FIELD = 'sent_mail_read';


    /**
     * @throws NotFoundException
     */
    public function go()
    {
        $documentEmail = $this->objectInstancier->getInstance(DocumentEmail::class);

        $this->getDonneesFormulaire()->setData(
            $this->getMappingValue(self::READ_MAIL_NUMBER_FIELD),
            $documentEmail->getNumberOfMailRead($this->id_d)
        );

        return true;
    }
}
