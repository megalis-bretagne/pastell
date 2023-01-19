<?php

namespace Pastell\Tests\Service\Document;

use DocumentEmail;
use DocumentEmailReponseSQL;
use DocumentSQL;
use Pastell\Service\Document\DocumentEmailService;
use PastellTestCase;

class DocumentEmailServiceTest extends PastellTestCase
{
    public function testGetDocumentEmailFromReponse(): void
    {
        $id_d = $this->createDocument('mailsec-bidir')['id_d'];
        $documentEmail = $this->getObjectInstancier()->getInstance(DocumentEmail::class);
        $key = $documentEmail->add($id_d, "foo@bar.com", "to");
        $id_de = $documentEmail->getInfoFromKey($key)['id_de'];
        $id_d_reponse = $this->createDocument('test')['id_d'];
        $documentEmailResponse = $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class);
        $documentEmailResponse->addDocumentReponseId($id_de, $id_d_reponse);
        $documentEmailResponse->validateReponse($id_de);

        $documentEmailService = $this->getObjectInstancier()->getInstance(DocumentEmailService::class);
        $this->assertEquals(
            $documentEmailService->getDocumentEmailFromIdReponse($id_d_reponse),
            $this->getObjectInstancier()->getInstance(DocumentSQL::class)->getInfo($id_d)
        );
    }
}
