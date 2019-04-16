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
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$info = $typeDossierImportExport->importFromFilePath(
			__DIR__."/../pastell-core/type-dossier/fixtures/cas-nominal.json"
		);
		return $info['id_t'];
	}

	/**
	 * @throws Exception
	 */
	public function testExportAction(){
		$id_t = $this->getTypeDossierId();
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$typeDossierImportExport->setTimeFunction(function(){return "42";});
		$this->setGetInfo(['id_t'=>$id_t]);
		$this->expectOutputRegex("#cas-nominal.json#");
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
			$this->assertRegexp("#Le type de dossier <b>cas-nominal</b> à été supprimé#",$e->getMessage());
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

		$this->getObjectInstancier()->getInstance(RoleSQL::class)->addDroit('admin',"cas-nominal:lecture");
		$this->getObjectInstancier()->getInstance(RoleSQL::class)->addDroit('admin',"cas-nominal:edition");
		$this->getObjectInstancier()->getInstance(RoleUtilisateur::class)->deleteCache(1,1);

		$this->createDocument('cas-nominal');

		$this->assertTrue($typeDossierSQL->exists($id_t));
		$this->assertFileExists($typeDossierPersonnaliseDirectoryManager->getTypeDossierPath($id_t));

		$this->setGetInfo(['id_t'=>$id_t]);
		try {
			$this->getTypeDossierController()->doDeleteAction();
			$this->assertFalse(true);
		} catch (Exception $e){
			$this->assertRegexp("#Le type de dossier <b>cas-nominal</b> est utilisé par des documents présent dans la base de données : La suppression est impossible.#",$e->getMessage());
		}
		$this->assertTrue($typeDossierSQL->exists($id_t));
		$this->assertFileExists($typeDossierPersonnaliseDirectoryManager->getTypeDossierPath($id_t));
	}

	/**
	 * @throws Exception
	 */
	public function testDoEditionAction(){
		$id_type_dossier = 'test-52';
		$this->getTypeDossierController();
		$this->setGetInfo(['id_type_dossier'=>$id_type_dossier]);
		try {
			$this->getTypeDossierController()->doEditionAction();
			$this->assertFalse(true);
		} catch (Exception $e){
			$this->assertRegExp("#Le type de dossier personnalisé <b>$id_type_dossier</b> a été créé#",$e->getMessage());
		}

		$typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);
		$id_t = $typeDossierSQL->getByIdTypeDossier($id_type_dossier);
		$this->assertEquals($id_type_dossier,$typeDossierSQL->getInfo($id_t)['id_type_dossier']);
	}

	/**
	 * @throws Exception
	 */
	public function testDoEditionActionWhenIdTypeDossierIsNull(){
		try {
			$this->getTypeDossierController()->doEditionAction();
			$this->assertFalse(true);
		} catch (Exception $e){
			$this->assertRegExp("#Aucun identifiant de type de dossier fourni#",$e->getMessage());
		}
	}

	/**
	 * @throws Exception
	 */
	public function testDoEditionActionWhenIdTypeDossierDoesNotMatchRegexp(){
		$this->getTypeDossierController();
		$this->setGetInfo(['id_type_dossier'=>'AAAAA']);
		try {
			$this->getTypeDossierController()->doEditionAction();
			$this->assertFalse(true);
		} catch (Exception $e){
			$this->assertRegExp("#L'identifiant du type de dossier ne respecte pas l'expression rationnelle#",$e->getMessage());
		}
	}
	/**
	 * @throws Exception
	 */
	public function testDoEditionActionWhenIdTypeDossierOverflowMaxLength(){
		$this->getTypeDossierController();
		$this->setGetInfo(['id_type_dossier'=>str_repeat("a",33)]);
		try {
			$this->getTypeDossierController()->doEditionAction();
			$this->assertFalse(true);
		} catch (Exception $e){
			$this->assertRegExp("#L'identifiant du type de dossier ne doit pas dépasser 32 caractères#",$e->getMessage());
		}
	}

}