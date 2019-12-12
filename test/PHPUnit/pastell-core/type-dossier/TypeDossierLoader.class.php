<?php

class TypeDossierLoader
{

    private $workspacePath;
    private $typeDossierSQL;
    private $typeDossierDefinition;
    private $memoryCache;
    private $extensionLoader;
    private $roleSQL;
    private $roleUtilisateur;

    private $tmp_folder;
    private $typeDossierImportExport;

    public function __construct(
        $workspacePath,
        TypeDossierSQL $typeDossierSQL,
        TypeDossierService $typeDossierDefinition,
        MemoryCache $memoryCache,
        ExtensionLoader $extensionLoader,
        RoleSQL $roleSQL,
        RoleUtilisateur $roleUtilisateur,
        TypeDossierImportExport $typeDossierImportExport
    ){
        $this->workspacePath = $workspacePath;
        $this->typeDossierSQL = $typeDossierSQL;
        $this->typeDossierDefinition = $typeDossierDefinition;
        $this->memoryCache = $memoryCache;
        $this->extensionLoader = $extensionLoader;
        $this->roleSQL = $roleSQL;
        $this->roleUtilisateur = $roleUtilisateur;
        $this->typeDossierImportExport = $typeDossierImportExport;
    }

    /**
     * La fonction glob() permet pas de rechercher dans le VFS, du coup, la génération dynamique
     * des fichiers de definition YAML n'est pas opérante à travers DocumentTypeFactory...
     *
     * Contournement : on réécrit le fichier quelque part et on charge le module...
     *
     * @param $type_dossier
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function createTypeDossierDefinitionFile($type_dossier)
    {
        $this->createTypeDossierFromFilepath(__DIR__ . "/fixtures/{$type_dossier}.json");
    }

    /**
     * La fonction glob() permet pas de rechercher dans le VFS, du coup, la génération dynamique
     * des fichiers de definition YAML n'est pas opérante à travers DocumentTypeFactory...
     *
     * Contournement : on réécrit le fichier quelque part et on charge le module...
     *
     * @param $definition_filepath
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function createTypeDossierFromFilepath($definition_filepath)
    {
        $this->memoryCache->delete('pastell_all_module');

        $tmpFolder = new TmpFolder();
        $this->tmp_folder = $tmpFolder->create();

        $info = $this->typeDossierImportExport->importFromFilePath($definition_filepath);

        $type_dossier = $info[TypeDossierImportExport::ID_TYPE_DOSSIER];

        mkdir($this->tmp_folder . "/module/$type_dossier/", 0777, true);
        copy(
            $this->workspacePath . "/" . TypeDossierPersonnaliseDirectoryManager::SUB_DIRECTORY . "/module/{$type_dossier}/definition.yml",
            $this->tmp_folder . "/module/{$type_dossier}/definition.yml"
        );

        $this->extensionLoader->loadExtension([$this->tmp_folder]);


        $this->roleSQL->addDroit('admin', "{$type_dossier}:lecture");
        $this->roleSQL->addDroit('admin', "{$type_dossier}:edition");
        $this->roleUtilisateur->deleteCache(1, 1);
    }

    public function unload()
    {
        if (!$this->tmp_folder) {
            return;
        }
        $tmpFolder = new TmpFolder();
        $tmpFolder->delete($this->tmp_folder);
        $this->memoryCache->delete('pastell_all_module');
    }
}
