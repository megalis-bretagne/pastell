<?php

class TypeDossierLoader {

	private $workspacePath;
	private $typeDossierSQL;
	private $typeDossierDefinition;
	private $memoryCache;
	private $extensionLoader;
	private $roleSQL;

	private $tmp_folder;

	public function __construct(
		$workspacePath,
		TypeDossierSQL $typeDossierSQL,
		TypeDossierDefinition $typeDossierDefinition,
		MemoryCache $memoryCache,
		ExtensionLoader $extensionLoader,
		RoleSQL $roleSQL
	) {
		$this->workspacePath = $workspacePath;
		$this->typeDossierSQL = $typeDossierSQL;
		$this->typeDossierDefinition = $typeDossierDefinition;
		$this->memoryCache = $memoryCache;
		$this->extensionLoader = $extensionLoader;
		$this->roleSQL = $roleSQL;
	}

	/**
	 * La fonction glob() permet pas de rechercher dans le VFS, du coup, la génération dynamique
	 * des fichiers de definition YAML n'est pas opérante à travers DocumentTypeFactory...
	 *
	 * Contournement : on réécrit le fichier quelque part et on charge le module...
	 *
	 * @throws Exception
	 */
	public function createTypeDossierDefinitionFile($type_dossier){
		$tmpFolder = new TmpFolder();
		$this->tmp_folder = $tmpFolder->create();
		$id_t = $this->typeDossierSQL->edit(0,$type_dossier);

		copy(
			__DIR__."/fixtures/type_dossier_{$type_dossier}.json",
			$this->workspacePath."/type_dossier_$id_t.json"
		);

		$this->typeDossierDefinition->reGenerate($id_t);

		mkdir($this->tmp_folder."/module/{$type_dossier}/",0777,true);
		copy($this->workspacePath."/".TypeDossierPersonnaliseDirectoryManager::SUB_DIRECTORY."/module/{$type_dossier}/definition.yml",
			$this->tmp_folder."/module/{$type_dossier}/definition.yml");

		$this->extensionLoader->loadExtension([$this->tmp_folder]);

		$this->roleSQL->addDroit('admin',"{$type_dossier}:lecture");
		$this->roleSQL->addDroit('admin',"{$type_dossier}:edition");

		return $this->tmp_folder;
	}

	public function unload(){
		if (! $this->tmp_folder){
			return;
		}
		$tmpFolder = new TmpFolder();
		$tmpFolder->delete($this->tmp_folder);
		$this->memoryCache->delete('pastell_all_module');
	}

}