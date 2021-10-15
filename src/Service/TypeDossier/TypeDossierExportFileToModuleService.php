<?php

namespace Pastell\Service\TypeDossier;

use Symfony\Component\Filesystem\Filesystem;
use TypeDossierPersonnaliseDirectoryManager;

class TypeDossierExportFileToModuleService
{
    private $typeDossierImportService;

    /**
     * @var TypeDossierPersonnaliseDirectoryManager
     */
    private $typeDossierPersonnaliseDirectoryManager;

    public function __construct(
        TypeDossierImportService $typeDossierImportService,
        TypeDossierPersonnaliseDirectoryManager $typeDossierPersonnaliseDirectoryManager
    ) {
        $this->typeDossierImportService = $typeDossierImportService;
        $this->typeDossierPersonnaliseDirectoryManager = $typeDossierPersonnaliseDirectoryManager;
    }

    /**
     * @param string $input_file_path
     * @param string $export_dir_path
     */
    public function export(string $input_file_path, string $export_dir_path)
    {
        $file_content = file_get_contents($input_file_path);
        $properties = $this->typeDossierImportService->getInfoFromFileContent($file_content);

        $id_type_dossier = $properties[1];
        $typeDossierProperties = $properties[2];

        $final_dir_path = $export_dir_path . "/" . $id_type_dossier;

        $filesystem = new Filesystem();
        $filesystem->mkdir($final_dir_path);

        $this->typeDossierPersonnaliseDirectoryManager->saveToDir($final_dir_path, $typeDossierProperties, $input_file_path);
    }
}
