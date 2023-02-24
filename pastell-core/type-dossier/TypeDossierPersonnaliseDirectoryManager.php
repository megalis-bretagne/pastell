<?php

use Symfony\Component\Filesystem\Filesystem;

class TypeDossierPersonnaliseDirectoryManager
{
    public const SUB_DIRECTORY = 'type-dossier-personnalise';

    private $ymlLoader;
    private $workspace_path;
    private $typeDossierSQL;
    private $typeDossierTranslator;

    public function __construct(
        YMLLoader $yml_loader,
        $workspacePath,
        TypeDossierSQL $typeDossierSQL,
        TypeDossierTranslator $typeDossierTranslator
    ) {
        $this->ymlLoader = $yml_loader;
        $this->workspace_path = $workspacePath;
        $this->typeDossierSQL = $typeDossierSQL;
        $this->typeDossierTranslator = $typeDossierTranslator;
    }

    /**
     * @param int $id_t
     * @param TypeDossierProperties $typeDossierData
     * @throws Exception
     */
    public function save(int $id_t, TypeDossierProperties $typeDossierData): void
    {
        $type_dossier_directory = $this->getTypeDossierPath($id_t);
        $this->saveToDir($type_dossier_directory, $typeDossierData);
    }

    /**
     * @param $type_dossier_directory
     * @param TypeDossierProperties $typeDossierData
     * @throws Exception
     */
    public function saveToDir($type_dossier_directory, TypeDossierProperties $typeDossierData, string $input_file_path = ''): void
    {
        $filesystem = new Filesystem();
        if (! $filesystem->exists($type_dossier_directory)) {
            $filesystem->mkdir($type_dossier_directory);
        }

        $type_dossier_definition_content = $this->typeDossierTranslator->getDefinition($typeDossierData);
        if ($input_file_path) {
            $type_dossier_definition_content['studio_definition'] = base64_encode(file_get_contents($input_file_path));
        }

        $this->ymlLoader->saveArray(
            $type_dossier_directory . "/" . FluxDefinitionFiles::DEFINITION_FILENAME,
            $type_dossier_definition_content
        );
    }

    /**
     * @param $id_t
     * @return string
     * @throws TypeDossierException
     */
    public function getTypeDossierPath($id_t): string
    {
        $info = $this->typeDossierSQL->getInfo($id_t);
        if (! $info) {
            throw new TypeDossierException("Impossible de trouver l'emplacement du type de dossier $id_t");
        }

        return $this->getPathToTypeDossier($info['id_type_dossier']);
    }

    /**
     * @param $id_t
     * @throws TypeDossierException
     */
    public function delete($id_t)
    {
        $dossier_path = $this->getTypeDossierPath($id_t);
        $filesystem = new Filesystem();
        $filesystem->remove($dossier_path);
    }

    /**
     * @param $source_type_dossier_id
     * @param $target_type_dossier_id
     * @throws TypeDossierException
     */
    public function rename($source_type_dossier_id, $target_type_dossier_id)
    {
        $filesystem = new Filesystem();
        $source_type_dossier_directory = $this->getPathToTypeDossier($source_type_dossier_id);
        $target_type_dossier_directory = $this->getPathToTypeDossier($target_type_dossier_id);
        if (!$filesystem->exists($source_type_dossier_directory)) {
            throw new TypeDossierException("Impossible de trouver l'emplacement du type de dossier $source_type_dossier_id");
        }
        if ($filesystem->exists($target_type_dossier_directory)) {
            throw new TypeDossierException("L'emplacement du type de dossier « $target_type_dossier_id » est déjà utilisé.");
        }

        $filesystem->rename($source_type_dossier_directory, $target_type_dossier_directory);
    }

    /**
     * @param $id_type_dossier_source
     * @return string
     */
    private function getPathToTypeDossier($id_type_dossier_source): string
    {
        return $this->workspace_path . "/" . self::SUB_DIRECTORY . "/module/$id_type_dossier_source";
    }
}
