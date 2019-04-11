<?php

use Symfony\Component\Filesystem\Filesystem;

class  TypeDossierPersonnaliseDirectoryManager {

	const SUB_DIRECTORY = 'type-dossier-personnalise';

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
	public function save($id_t, TypeDossierProperties $typeDossierData){
		$type_dossier_directory = $this->getTypeDossierPath($id_t);
		$filesystem = new Filesystem();
		if (! $filesystem->exists($type_dossier_directory)){
			$filesystem->mkdir($type_dossier_directory);
		}

		$type_dossier_definition_content = $this->typeDossierTranslator->getDefinition($typeDossierData);


		$this->ymlLoader->saveArray(
			$type_dossier_directory."/".FluxDefinitionFiles::DEFINITION_FILENAME,
			$type_dossier_definition_content
		);
	}

	public function getTypeDossierPath($id_t){
		$info = $this->typeDossierSQL->getInfo($id_t);
		return $this->workspace_path."/".self::SUB_DIRECTORY."/module/{$info['id_type_dossier']}";
	}

	public function delete($id_t){
		$dossier_path = $this->getTypeDossierPath($id_t);
		$filesystem = new Filesystem();
		$filesystem->remove($dossier_path);
	}

}