<?php

class MailsecComputeAnsweredMail extends ConnecteurTypeActionExecutor
{
    private const ANSWERED_MAIL_NUMBER_FIELD = 'sent_mail_answered';


    /**
     * @throws NotFoundException
     */
    public function go()
    {
        $documentEmailReponseSql = $this->objectInstancier->getInstance(DocumentEmailReponseSQL::class);

        $this->getDonneesFormulaire()->setData(
            $this->getMappingValue(self::ANSWERED_MAIL_NUMBER_FIELD),
            $documentEmailReponseSql->getNumberOfAnsweredMail($this->id_d)
        );

        return true;
    }
}
