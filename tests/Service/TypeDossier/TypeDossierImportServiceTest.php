<?php

namespace Pastell\Tests\Service\TypeDossier;

use Pastell\Service\TypeDossier\TypeDossierImportService;
use Pastell\Service\TypeDossier\TypeDossierUtilService;
use PastellTestCase;
use TypeDossierException;

class TypeDossierImportServiceTest extends PastellTestCase
{
    public const FIXTURE_FILE = __DIR__ . "/fixtures/arrete-rh.json";
    public const ID_TYPE_DOSSIER = 'arrete-rh';

    public function testImport()
    {
        $typeDossierImportService = $this->getObjectInstancier()->getInstance(TypeDossierImportService::class);
        $result = $typeDossierImportService->import(file_get_contents(self::FIXTURE_FILE));
        unset($result[TypeDossierUtilService::TIMESTAMP]);
        $this->assertEquals(
            [
                'id_t' => '1',
                'id_type_dossier' =>  self::ID_TYPE_DOSSIER,
                'orig_id_type_dossier' => self::ID_TYPE_DOSSIER,
             ],
            $result
        );
    }

    public function testImportWhenNoContent()
    {
        $typeDossierImportService = $this->getObjectInstancier()->getInstance(TypeDossierImportService::class);
        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage("La définition du type de dossier est vide");
        $typeDossierImportService->import("");
    }

    public function testImportWhenNoJson()
    {
        $typeDossierImportService = $this->getObjectInstancier()->getInstance(TypeDossierImportService::class);
        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage("La définition json du type de dossier n'est pas valide");
        $typeDossierImportService->import("foo");
    }

    public function testImportWhenJsonIsNotExploitable()
    {
        $typeDossierImportService = $this->getObjectInstancier()->getInstance(TypeDossierImportService::class);
        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage("La définition json du type de dossier ne semble pas contenir de données utilisables");
        $typeDossierImportService->import('{"toto":"toto"}');
    }

    public function testDoubleImport()
    {
        $typeDossierImportService = $this->getObjectInstancier()->getInstance(TypeDossierImportService::class);

        $typeDossierImportService->import(file_get_contents(self::FIXTURE_FILE));
        $result = $typeDossierImportService->import(file_get_contents(self::FIXTURE_FILE));
        unset($result[TypeDossierUtilService::TIMESTAMP]);
        $this->assertEquals(
            [
                'id_t' => '2',
                'id_type_dossier' => 'arrete-rh-1',
                'orig_id_type_dossier' => self::ID_TYPE_DOSSIER,
             ],
            $result
        );
    }
}
