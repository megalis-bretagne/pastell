<?php

namespace Pastell\Tests\Service\TypeDossier;

use Pastell\Service\TypeDossier\TypeDossierExportService;
use Pastell\Service\TypeDossier\TypeDossierImportService;
use Pastell\Service\TypeDossier\TypeDossierUtilService;
use PastellTestCase;

class TypeDossierExportServiceTest extends PastellTestCase
{
    public const FIXTURE_FILE = __DIR__ . "/fixtures/arrete-rh.json";
    public const ID_TYPE_DOSSIER = 'arrete-rh';

    public function testImportExport()
    {
        $typeDossierImportService = $this->getObjectInstancier()->getInstance(TypeDossierImportService::class);
        $result = $typeDossierImportService->import(file_get_contents(self::FIXTURE_FILE));

        $typeDossierExportService = $this->getObjectInstancier()->getInstance(TypeDossierExportService::class);
        $typeDossierExportService->setTimeFunction(function () use ($result) {
            return $result[TypeDossierUtilService::TIMESTAMP];
        });

        $result2 = $typeDossierExportService->export($result['id_t']);
        $this->assertEquals(json_decode(file_get_contents(self::FIXTURE_FILE), true), json_decode($result2, true));
    }
}
