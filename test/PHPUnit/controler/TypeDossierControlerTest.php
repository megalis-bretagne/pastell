<?php

class TypeDossierControlerTest extends ControlerTestCase {

	/**
	 * @return TypeDossierControler
	 */
	private function getTypeDossierController(){
		return $this->getControlerInstance(TypeDossierControler::class);
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	private function getTypeDossierId(){
		$this->getTypeDossierController();
		$typeDossierService = $this->getObjectInstancier()->getInstance(TypeDossierService::class);
		$id_t = $typeDossierService->create('cas_nominal');
		$typeDossierProperties = $typeDossierService->getTypeDossierPropertiesFromFilepath(__DIR__."/../pastell-core/type-dossier/fixtures/type_dossier_cas_nominal.json");



		$typeDossierService->save($id_t,$typeDossierProperties);
		return $id_t;
	}

	/**
	 * @throws Exception
	 */
	public function testExportAction(){
		$id_t = $this->getTypeDossierId();
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$typeDossierImportExport->setTimeFunction(function(){return "42";});
		$this->setGetInfo(['id_t'=>$id_t]);
		$this->expectOutputString(
			file_get_contents(__DIR__."/fixtures/type-dossier-controler-export-expected.txt")
		);
		$this->getTypeDossierController()->exportAction();
	}

	/**
	 * @throws Exception
	 */
	public function testDoDeleteAction(){

		$typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);
		$typeDossierPersonnaliseDirectoryManager = $this->getObjectInstancier()->getInstance(TypeDossierPersonnaliseDirectoryManager::class);

		$id_t = $this->getTypeDossierId();
		$this->assertTrue($typeDossierSQL->exists($id_t));
		$this->assertFileExists($typeDossierPersonnaliseDirectoryManager->getTypeDossierPath($id_t));

		$this->setGetInfo(['id_t'=>$id_t]);
		try {
			$this->getTypeDossierController()->doDeleteAction();
			$this->assertFalse(true);
		} catch (Exception $e){
			$this->assertRegexp("#Le type de dossier <b>cas_nominal</b> à été supprimé#",$e->getMessage());
		}
		$this->assertFalse($typeDossierSQL->exists($id_t));
		$this->assertFileNotExists($typeDossierPersonnaliseDirectoryManager->getTypeDossierPath($id_t));
	}

	/**
	 * @throws Exception
	 */
	public function testDoDeleteActionWhenTypeDossierIsUsed(){
		$typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);
		$typeDossierPersonnaliseDirectoryManager = $this->getObjectInstancier()->getInstance(TypeDossierPersonnaliseDirectoryManager::class);

		$id_t = $this->getTypeDossierId();

		$this->getObjectInstancier()->getInstance(RoleSQL::class)->addDroit('admin',"cas_nominal:lecture");
		$this->getObjectInstancier()->getInstance(RoleSQL::class)->addDroit('admin',"cas_nominal:edition");
		$this->getObjectInstancier()->getInstance(RoleUtilisateur::class)->deleteCache(1,1);

		$this->createDocument('cas_nominal');

		$this->assertTrue($typeDossierSQL->exists($id_t));
		$this->assertFileExists($typeDossierPersonnaliseDirectoryManager->getTypeDossierPath($id_t));

		$this->setGetInfo(['id_t'=>$id_t]);
		try {
			$this->getTypeDossierController()->doDeleteAction();
			$this->assertFalse(true);
		} catch (Exception $e){
			$this->assertRegexp("#Le type de dossier <b>cas_nominal</b> est utilisé par des documents présent dans la base de données : La suppression est impossible.#",$e->getMessage());
		}
		$this->assertTrue($typeDossierSQL->exists($id_t));
		$this->assertFileExists($typeDossierPersonnaliseDirectoryManager->getTypeDossierPath($id_t));
	}

}