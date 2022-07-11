<?php

namespace Pastell\Tests\Command\Studio;

use Exception;
use Pastell\Service\TypeDossier\TypeDossierExportFileToModuleService;
use PastellTestCase;
use Symfony\Component\Finder\Finder;
use TmpFolder;
use TypeDossierException;

class PastellPackTest extends PastellTestCase
{
    private array $moduleDefinition = [
        'dossier-wgfc' => ['', 'gfc-dossier', "Dossier GFC"],
        'dossier-wgfc-destinataire' => ['', 'gfc-dossier-destinataire', 'Dossier GFC (destinataire)'],
        'dossier-autorisation-urba-draft' => [
            'pack_urbanisme',
            'dossier-autorisation-urbanisme',
            "Archivage des dossiers d'autorisation d'urbanisme"
        ],
        'document-autorisation-urba-draft' => [
            'pack_urbanisme',
            'document-autorisation-urbanisme',
            "Document d'autorisation d'urbanisme"
        ],
        'document-autorisation-urba-destinataire-draft' => [
            'pack_urbanisme',
            'document-autorisation-urbanisme-destinataire',
            "Document d'autorisation d'urbanisme (destinataire)"
        ],
    ];

    public function jsonProvider(): array
    {
        $result = [];
        $finder = new Finder();
        $paths = $finder->in(__DIR__ . "/../../../pack-json/")->files()->name('*.json');
        foreach ($paths as $file) {
            $data = [$file->getPathname()];
            $data = array_merge(
                $data,
                $this->moduleDefinition[$file->getFilenameWithoutExtension()]
                ??
                ['',$file->getFilenameWithoutExtension(),'']
            );
            $result[$file->getFilenameWithoutExtension()] = $data;
        }
        return $result;
    }

    /**
     * @dataProvider jsonProvider
     * @param string $jsonFilepath
     * @param string $restrictionPack
     * @param string $moduleId
     * @param string $moduleName
     * @return void
     * @throws TypeDossierException
     * @throws Exception
     */
    public function testAllPack(
        string $jsonFilepath,
        string $restrictionPack,
        string $moduleId,
        string $moduleName
    ): void {
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        $typeDossierExportFileToModuleService =
            $this->getObjectInstancier()->getInstance(TypeDossierExportFileToModuleService::class);
        $typeDossierExportFileToModuleService->export(
            $jsonFilepath,
            $tmp_folder,
            $restrictionPack,
            $moduleId,
            $moduleName
        );

        self::assertFileEquals(
            __DIR__ . "/../../../module/$moduleId/definition.yml",
            $tmp_folder . "/$moduleId/definition.yml"
        );
        $tmpFolder->delete($tmp_folder);
    }
}
