<?php

namespace Pastell\Service\Document;

use DocumentEmail;
use DocumentEmailReponseSQL;
use DocumentSQL;

class DocumentEmailService
{
    private $documentEmail;
    private $documentEmailReponseSQL;
    private $documentSQL;

    public function __construct(
        DocumentEmail $documentEmail,
        DocumentEmailReponseSQL $documentEmailReponseSQL,
        DocumentSQL $documentSQL
    ) {
        $this->documentEmail = $documentEmail;
        $this->documentEmailReponseSQL = $documentEmailReponseSQL;
        $this->documentSQL = $documentSQL;
    }

    public function getDocumentEmailFromIdReponse(string $documentId): array
    {
        $mailInfo = [];
        $reponse = $this->documentEmailReponseSQL->getInfoFromIdReponse($documentId);
        if (!empty($reponse)) {
            $mail = $this->documentEmail->getInfoFromPK($reponse['id_de']);
        }
        if (!empty($mail)) {
            $mailInfo = $this->documentSQL->getInfo($mail['id_d']);
        }
        return $mailInfo;
    }
}
