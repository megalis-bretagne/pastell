<?php

namespace Pastell\Tests\Service\TypeDossier;

use Pastell\Service\TypeDossier\TypeDossierExportService;
use Pastell\Service\TypeDossier\TypeDossierImportService;
use Pastell\Service\TypeDossier\TypeDossierUtilService;
use PastellTestCase;
use TypeDossierException;

class TypeDossierExportServiceTest extends PastellTestCase
{
    private const FIXTURE_FILE = __DIR__ . "/fixtures/arrete-rh.json";
    public const ID_TYPE_DOSSIER = 'arrete-rh';

    /**
     * @throws TypeDossierException
     */
    public function testImportExport()
    {
        $typeDossierImportService = $this->getObjectInstancier()->getInstance(TypeDossierImportService::class);
        $result = $typeDossierImportService->import(file_get_contents(self::FIXTURE_FILE));

        $typeDossierExportService = $this->getObjectInstancier()->getInstance(TypeDossierExportService::class);
        $typeDossierExportService->setTimeFunction(function () use ($result) {
            return $result[TypeDossierUtilService::TIMESTAMP];
        });

        $result2 = $typeDossierExportService->export($result['id_t']);
//        file_put_contents(self::FIXTURE_FILE,$result2);
        $this->assertJsonStringEqualsJsonFile(self::FIXTURE_FILE, $result2);
    }
}
