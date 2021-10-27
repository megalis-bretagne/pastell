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
     * @param string $restriction_pack
     * @param string $module_name
     * @throws \TypeDossierException
     */
    public function export(string $input_file_path, string $export_dir_path, string $restriction_pack, string $module_id, string $module_name)
    {
        $file_content = file_get_contents($input_file_path);
        $properties = $this->typeDossierImportService->getInfoFromFileContent($file_content);

        $id_type_dossier = $module_id ?: $properties[1];
        /** @var \TypeDossierProperties $typeDossierProperties */
        $typeDossierProperties = $properties[2];

        $typeDossierProperties->id_type_dossier = $id_type_dossier;
        $typeDossierProperties->nom = $module_name ?: $typeDossierProperties->nom;
        $typeDossierProperties->restriction_pack = $restriction_pack;

        $final_dir_path = $export_dir_path . "/" . $id_type_dossier;

        $filesystem = new Filesystem();
        $filesystem->mkdir($final_dir_path);

        $this->typeDossierPersonnaliseDirectoryManager->saveToDir($final_dir_path, $typeDossierProperties, $input_file_path);
    }
}
