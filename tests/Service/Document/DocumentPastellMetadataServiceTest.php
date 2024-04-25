<?php

declare(strict_types=1);

namespace Pastell\Tests\Service\Document;

use Pastell\Service\Document\DocumentPastellMetadataService;
use PastellTestCase;

class DocumentPastellMetadataServiceTest extends PastellTestCase
{
    public function documentPastellMetadataService(): DocumentPastellMetadataService
    {
        return $this->getObjectInstancier()->getInstance(DocumentPastellMetadataService::class);
    }

    public function testGetMetadataPastellByDocument(): void
    {
        $document = $this->createDocument('test');

        static::assertSame(
            [
                'pa_document_id_d' => $document['id_d'],
                'pa_document_titre' => '',
                'pa_document_creation_date' => $document['info']['creation'],
                'pa_creator_id_u' => 1,
                'pa_creator_lastname' => 'Pommateau',
                'pa_creator_firstname' => 'Eric',
                'pa_creator_email' => 'eric@sigmalis.com',
                'pa_creator_login' => 'admin',
                'pa_entity_id_e' => 1,
                'pa_entity_name' => 'Bourg-en-Bresse',
                'pa_entity_siren' => '000000000',
            ],
            $this->documentPastellMetadataService()->getMetadataPastellByDocument($document['id_d'])
        );
    }
}
