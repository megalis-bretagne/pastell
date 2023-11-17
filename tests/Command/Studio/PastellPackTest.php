<?php

namespace Pastell\Tests\Command\Studio;

use Exception;
use Pastell\Service\TypeDossier\TypeDossierExportFileToModuleService;
use PastellTestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TmpFolder;
use TypeDossierException;

class PastellPackTest extends PastellTestCase
{
    private array $moduleDefinition = [
        'dossier-wgfc' => ['', 'gfc-dossier', 'Dossier GFC'],
        'dossier-wgfc-destinataire' => ['', 'gfc-dossier-destinataire', 'Dossier GFC (destinataire)'],
        'dossier-autorisation-urba-draft' => [
            'pack_urbanisme',
            'dossier-autorisation-urbanisme',
            "Dossiers d'autorisation d'urbanisme (archivage)"
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
        'draft-rh-document-individuel' => [
            'pack_rh',
            'rh-document-individuel',
            'Document individuel'
        ],
        'draft-rh-document-individuel-destinataire' => [
            'pack_rh',
            'rh-document-individuel-destinataire',
            'Document individuel (destinataire)'
        ],
        'draft-rh-bulletin-salaire' => [
            'pack_rh',
            'rh-bulletin-salaire',
            'Bulletin de salaire'
        ],
        'draft-rh-bulletin-salaire-destinataire' => [
            'pack_rh',
            'rh-bulletin-salaire-destinataire',
            'Bulletin de salaire (destinataire)',
        ],
        'draft-rh-archivage-dossier-agent' => [
            'pack_rh',
            'rh-archivage-dossier-agent',
            "Eléments du dossier individuel de l'agent (archivage)"
        ],
        'draft-rh-archivage-collectif' => [
            'pack_rh',
            'rh-archivage-collectif',
            'Données de gestion collective (fichier unitaire) (archivage)'
        ],
        'draft-rh-archivage-collectif-zip' => [
            'pack_rh',
            'rh-archivage-collectif-zip',
            'Données de gestion collective (fichier compressé) (archivage)'
        ],
        'ls-actes-publication-draft' => [
            '',
            'ls-actes-publication',
            'Actes publication',
        ],
        'ls-dossier-seance-draft' => [
            '',
            'ls-dossier-seance',
            'Dossier de séance (archivage)',
        ],
        'ls-document-pdf-draft' => [
            '',
            'ls-document-pdf',
            'Document PDF',
        ],
        'ls-recup-parapheur-draft' =>
        [
            '',
            'ls-recup-parapheur',
            'Récupération parapheur',
        ],
        'ls-document-pdf-draft-destinataire' => [
            '',
            'ls-document-pdf-destinataire',
            'Document PDF (destinataire)',
        ],
    ];

    public function jsonProvider(): array
    {
        $result = [];
        $finder = new Finder();
        $paths = $finder->in(__DIR__ . '/../../../pack-json/')->files()->name('*.json');
        /** @var SplFileInfo $file */
        foreach ($paths as $file) {
            $data = array_merge(
                [$file->getPathname()],
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

//        \file_put_contents(
//            __DIR__ . "/../../../module/$moduleId/definition.yml",
//            \file_get_contents($tmp_folder . "/$moduleId/definition.yml"),
//        );

        self::assertFileEquals(
            __DIR__ . "/../../../module/$moduleId/definition.yml",
            $tmp_folder . "/$moduleId/definition.yml"
        );
        $tmpFolder->delete($tmp_folder);
    }
}
