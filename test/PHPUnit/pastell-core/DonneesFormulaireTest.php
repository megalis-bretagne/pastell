<?php

class DonneesFormulaireTest extends PastellTestCase {

	/**
	 * @return DonneesFormulaire
	 * @throws Exception
	 */
	private function getDonneesFormulaire(){
		return $this->getDonneesFormulaireFactory()->get('toto','test');
	}
	
	/**
	 * @throws Exception
	 * @var $password
	 * @dataProvider getPassword
	 */
	public function testPassword($password){
		$recuperateur = new Recuperateur(array('password'=>$password));
		$this->getDonneesFormulaire()->saveTab($recuperateur, new FileUploader(), 0);
		$this->assertEquals($password,$this->getDonneesFormulaire()->get('password'));
	}
	
	public function getPassword(){
		return array(
				array('215900689B')
		);
	}

	/*
	 * Bon, ben il semblerait que ca soit fait exprès...  
	 public function testTrue(){
		$this->getDonneesFormulaire()->setData('foo','true');
		$this->assertEquals('true',$this->getDonneesFormulaire()->get('foo'));
	}
	*/

	private function getDonneesFormulaireChampsCache(){
		$filePath = $this->getObjectInstancier()->{'workspacePath'}."/YZZT.yml";
		$ymlLoader = new YMLLoader(new MemoryCacheNone());
		$module_definition = $ymlLoader->getArray(__DIR__."/../fixtures/definition-champs-cache.yml");
		$documentType = new DocumentType("test-fichier", $module_definition);
		return new DonneesFormulaire($filePath, $documentType);
	}
	
	public function testModifOngletCache(){
		$donneesFormulaire = $this->getDonneesFormulaireChampsCache();
		$donneesFormulaire->setData("chaine","12");
		$this->assertEquals("12",$donneesFormulaire->get("chaine"));
	}

	/**
	 * @throws Exception
	 */
	public function testModifOngletCacheFichier(){
		$donneesFormulaire = $this->getDonneesFormulaireChampsCache();
		$donneesFormulaire->addFileFromData("fichier_visible", "test.txt", "texte");
		$this->assertEquals("texte",$donneesFormulaire->getFileContent("fichier_visible"));
	}
	
	public function testSaveAllFile(){
		$donneesFormulaire = $this->getDonneesFormulaireChampsCache();
		$donneesFormulaire->setData("chaine","12");
		
		$file_path = $this->getObjectInstancier()->{'workspacePath'}."/test.txt";
		file_put_contents($file_path, "texte");
		
		$files = array('fichier_visible'=>array('tmp_name'=>$file_path,'error'=>UPLOAD_ERR_OK,'name'=>'test.txt'));
		
		$fileUploader = new FileUploader();
		$fileUploader->setFiles($files);
		$donneesFormulaire->saveAllFile($fileUploader);
		$this->assertEquals("texte",$donneesFormulaire->getFileContent('fichier_visible'));
	}
	
	public function testSaveAllFileHidden(){
		$donneesFormulaire = $this->getDonneesFormulaireChampsCache();
		$donneesFormulaire->setData("chaine","12");
	
		$file_path = $this->getObjectInstancier()->{'workspacePath'}."/test.txt";
		file_put_contents($file_path, "texte");
	
		$files = array('fichier_hidden'=>array('tmp_name'=>$file_path,'error'=>UPLOAD_ERR_OK,'name'=>'test.txt'));
	
		$fileUploader = new FileUploader();
		$fileUploader->setFiles($files);
		$donneesFormulaire->saveAllFile($fileUploader);
		$this->assertEquals("texte",$donneesFormulaire->getFileContent('fichier_hidden'));
	}


	/**
	 * @throws Exception
	 */
	public function testSerializeExport(){
		$donneesFormulaire = $this->getDonneesFormulaire();
		$donneesFormulaire->setData('foo','bar');
		$json = $donneesFormulaire->jsonExport();
		$info = json_decode($json,true);
		$this->assertEquals('bar',$info['metadata']['foo']);
	}

	/**
	 * @throws Exception
	 */
	public function testSerializeExportEmtpy(){
		$donneesFormulaire = $this->getDonneesFormulaire();
		$json = $donneesFormulaire->jsonExport();
		$info = json_decode($json,true);
		$this->assertEmpty($info['metadata']);
	}

	/**
	 * @throws Exception
	 */
	public function testSerializeExportFile(){
		$donneesFormulaire = $this->getDonneesFormulaire();
		$donneesFormulaire->setData('foo','bar');
		$file_content = "Hello World!";
		$donneesFormulaire->addFileFromData('fichier','test.txt',$file_content);
		$json = $donneesFormulaire->jsonExport();
		$info = json_decode($json,true);
		$this->assertEquals($file_content,base64_decode($info['file']['fichier'][0]));
	}

	/**
	 * @throws Exception
	 */
	public function testSerializeImport(){
		$donneesFormulaire = $this->getDonneesFormulaire();
		$donneesFormulaire->setData('foo','bar');
		$data = $donneesFormulaire->jsonExport();

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get("bar","baz");
		$this->assertFalse($donneesFormulaire->get('foo'));

		$donneesFormulaire->jsonImport($data);
		$this->assertEquals("bar",$donneesFormulaire->get('foo'));
	}

	/**
	 * @throws Exception
	 */
	public function testSerializeImportFile(){
		$donneesFormulaire = $this->getDonneesFormulaire();
		$donneesFormulaire->setData('foo','bar');
		$file_content = "Hello World!";
		$donneesFormulaire->addFileFromData('fichier','test.txt',$file_content);
		$data = $donneesFormulaire->jsonExport();

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get("bar","baz");
		$this->assertFalse($donneesFormulaire->get('fichier'));

		$donneesFormulaire->jsonImport($data);
		$this->assertEquals($file_content,$donneesFormulaire->getFileContent('fichier'));
	}

	/**
	 * @throws Exception
	 */
	public function testImportFileFailed(){
		$this->expectException(Exception::class);
		$this->expectExceptionMessage("Impossible de déchiffrer le fichier");
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get("bar","baz");
		$donneesFormulaire->jsonImport("toto");
	}

	/**
	 * @throws Exception
	 */
	public function testImportFileFailedJson(){
		$this->expectException(Exception::class);
		$this->expectExceptionMessage("Clé metadata absente du fichier");
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get("bar","baz");
		$donneesFormulaire->jsonImport(json_encode(array("foo"=>"bar")));
	}

	/**
	 * @throws Exception
	 */
	public function testImportFileNoFile(){
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get("bar","baz");
		$donneesFormulaire->jsonImport(json_encode(array("metadata"=>array("fichier"=>array(0=>"toto.txt")))));
		$this->assertEmpty($donneesFormulaire->getFileContent("fichier"));
		$this->assertEquals("toto.txt",$donneesFormulaire->getFileName("fichier"));
	}

	/**
	 * @throws Exception
	 */
	public function testGetFieldDataList(){
		$field_list = $this->getDonneesFormulaire()->getFieldDataList("editeur",0);
		/** @var FieldData $field */
		$field = $field_list[0];
		$this->assertEquals("Mot de passe",$field->getField()->getLibelle());
	}

	/**
	 * @throws Exception
	 */
	public function testGetFieldDataListEmptyOnglet(){
		$field_list = $this->getDonneesFormulaire()->getFieldDataList("editeur",2);
		$this->assertEmpty($field_list);
	}

	/**
	 * @throws Exception
	 */
	public function testGetFileNameWithoutExtension(){
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get("bar","baz");
		$donneesFormulaire->jsonImport(json_encode(array("metadata"=>array("fichier"=>array(0=>"toto.txt")))));
		$this->assertEquals("toto",$donneesFormulaire->getFileNameWithoutExtension("fichier"));
	}

	/**
	 * @throws Exception
	 */
	public function testGetWithDefault(){
		$this->assertEquals("Ceci est un autre texte de défaut",$this->getDonneesFormulaire()->getWithDefault('test_default_onglet_2'));
	}

	/**
	 * @throws Exception
	 */
	public function testGetWithDefaultWithout(){
		$this->getDonneesFormulaire()->setData('test_default_onglet_2',"foo");
		$this->assertEquals("foo",$this->getDonneesFormulaire()->getWithDefault('test_default_onglet_2'));
	}

	/**
	 * @throws Exception
	 */
	public function testGetWithDefaultEmpty(){
		$this->getDonneesFormulaire()->setData('test_default_onglet_2',"foo");
		$this->getDonneesFormulaire()->setData('test_default_onglet_2',"");
		$this->assertEquals("Ceci est un autre texte de défaut",$this->getDonneesFormulaire()->getWithDefault('test_default_onglet_2'));
	}

	public function testEmptyForms(){
		$documentType = new DocumentType("test", array());
		$donneesFormulaire = new DonneesFormulaire("/tmp/toto.yml",$documentType);
		$donneesFormulaire->setDocumentIndexor(new DocumentIndexor(new DocumentIndexSQL($this->getSQLQuery()),'1'));
		$donneesFormulaire->saveTab(new Recuperateur(),new FileUploader(),0);
		$this->assertTrue(true);
	}


}