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
	    return $this->getCustomDonneesFormulaire(__DIR__."/../fixtures/definition-champs-cache.yml");
	}

    /**
     * @param $path_to_yaml_definition
     * @return DonneesFormulaire
     */
    private function getCustomDonneesFormulaire($path_to_yaml_definition): DonneesFormulaire
    {
        $filePath = $this->getObjectInstancier()->{'workspacePath'} . "/YZZT.yml";
        $ymlLoader = new YMLLoader(new MemoryCacheNone());
        $module_definition = $ymlLoader->getArray($path_to_yaml_definition);
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

	/**
	 * @dataProvider copyFileProvider
	 * @throws Exception
	 */
	public function testCopyFile($filename){
		$tmpFolder = new TmpFolder();
		$tmp_folder = $tmpFolder->create();
		$donneesFormulaire = $this->getDonneesFormulaire();
		$donneesFormulaire->addFileFromData("fichier",$filename,"bar",0);
		$this->assertEquals([$filename],$donneesFormulaire->get("fichier"));

		$this->assertEquals(
			"$tmp_folder/$filename",
			$donneesFormulaire->copyFile('fichier',$tmp_folder)
		);
		$this->assertFileExists("$tmp_folder/$filename");
		$tmpFolder->delete($tmp_folder);
	}

	public function copyFileProvider(){
		return [
			['foo.txt'],
			['école.txt']
		];
	}

	/**
	 * @throws Exception
	 */
	public function testCopyFileFailed(){
		$donneesFormulaire = $this->getDonneesFormulaire();
		$this->assertFalse($donneesFormulaire->copyFile('fichier',"/tmp"));
	}

	/**
	 * @throws Exception
	 */
	public function testCopyFileNewName(){
		$tmpFolder = new TmpFolder();
		$tmp_folder = $tmpFolder->create();
		$donneesFormulaire = $this->getDonneesFormulaire();
		$donneesFormulaire->addFileFromData("fichier","foo.txt","bar",0);

		$this->assertEquals(
			"$tmp_folder/bar.txt",
			$donneesFormulaire->copyFile('fichier',$tmp_folder,0,"bar")
		);
		$this->assertFileExists("$tmp_folder/bar.txt");
		$tmpFolder->delete($tmp_folder);
	}

	/**
	 * @throws Exception
	 */
	public function testIndexation(){
		$id_d = $this->createDocument('helios-generique')['id_d'];

		$donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

		$donnesFormulaire->setData('id_bordereau','42');
		$donnesFormulaire->setData('id_coll','foo');
		$documentIndex = $this->getObjectInstancier()->getInstance(DocumentIndexSQL::class);
		$this->assertEquals('42',$documentIndex->get($id_d,'id_bordereau'));
		$this->assertEquals('foo',$documentIndex->get($id_d,'id_coll'));

		$sqlQuery = $this->getObjectInstancier()->getInstance(SQLQuery::class);
		$sqlQuery->setLogger($this->getLogger());

		$donnesFormulaire->setData('id_bordereau','bar');

		$this->assertEquals(5,count($this->getLogRecords()));

		$this->assertEquals('bar',$documentIndex->get($id_d,'id_bordereau'));
		$this->assertEquals('foo',$documentIndex->get($id_d,'id_coll'));
	}

    /**
     * @throws Exception
     */
    public function testGetContentTypeXml(){
        $id_d = $this->createDocument('helios-generique')['id_d'];

        $file_path = __DIR__."/fixtures/HELIOS_SIMU_ALR2_1496987735_826268894.xml";

        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donnesFormulaire->addFileFromCopy('fichier_pes', basename($file_path), $file_path);

        $this->assertEquals('application/xml',$donnesFormulaire->getContentType('fichier_pes'));

    }

    /**
     * @throws Exception
     */
    public function testMaxFileSize() {
        $donneesFormulaire = $this->getCustomDonneesFormulaire(__DIR__ . '/../fixtures/file-limit.yml');

        $ten_octets_string = '0123456789';
        $eleven_octets_string = '11 octets !';
        $donneesFormulaire->addFileFromData('file_10_octets','file1', $ten_octets_string);
        $this->assertTrue($donneesFormulaire->isValidable());

        $donneesFormulaire->addFileFromData('file_10_octets','file1', $eleven_octets_string);
        $this->assertFalse($donneesFormulaire->isValidable());
        $this->assertSame(
            'Le fichier «file1» (Fichier de 10 octets maximum) dépasse le poids limite autorisé : 10  octets, (11 trouvé)',
            $donneesFormulaire->getLastError()
        );
    }

    /**
     * @throws Exception
     */
    public function testMaxMultipleFileSize() {
        $donneesFormulaire = $this->getCustomDonneesFormulaire(__DIR__ . '/../fixtures/file-limit.yml');
        $ten_octets_string = '0123456789';
        $eleven_octets_string = '11 octets !';
        for ($i = 0; $i < 5; ++$i) {
            $donneesFormulaire->addFileFromData('multiple_file_50_octets', 'file_' . $i, $ten_octets_string, $i);
        }
        $this->assertTrue($donneesFormulaire->isValidable());

        $donneesFormulaire->addFileFromData('multiple_file_50_octets', 'file_4', $eleven_octets_string, 4);
        $this->assertFalse($donneesFormulaire->isValidable());
        $this->assertSame(
            'L\'ensemble des fichiers du champ multiple «Fichiers multiples dont la somme du poids est limité à 50 octets» dépasse le poids limite autorisé : (50)  octets, (51 trouvé)',
            $donneesFormulaire->getLastError()
        );
    }

    /**
     * @throws Exception
     */
    public function testMaxFileSizeOnMultipleField() {
        $donneesFormulaire = $this->getCustomDonneesFormulaire(__DIR__ . '/../fixtures/file-limit.yml');
        $ten_octets_string = '0123456789';
        $eleven_octets_string = '11 octets !';
        for ($i = 0; $i < 2; ++$i) {
            $donneesFormulaire->addFileFromData('multiple_file_10_octets_per_file', 'file_' . $i, $ten_octets_string, $i);
        }
        $this->assertTrue($donneesFormulaire->isValidable());

        $donneesFormulaire->addFileFromData('multiple_file_10_octets_per_file', 'file_3', $eleven_octets_string, 2);
        $this->assertFalse($donneesFormulaire->isValidable());
        $this->assertSame(
            'Le fichier «file_3» (multiple_file_10_octets_per_file) dépasse le poids limite autorisé : 10  octets, (11 trouvé)',
            $donneesFormulaire->getLastError()
        );
    }

    /**
     * @throws Exception
     */
    public function testThresholdSize() {
        $donneesFormulaire = $this->getCustomDonneesFormulaire(__DIR__ . '/../fixtures/file-limit.yml');
        $ten_octets_string = '0123456789';
        for ($i = 0; $i < 10; ++$i) {
            $donneesFormulaire->addFileFromData('multiple_file_10_octets_per_file', 'file_' . $i, $ten_octets_string, $i);
        }
        $this->assertTrue($donneesFormulaire->isValidable());

        $donneesFormulaire->addFileFromData('file_10_octets', 'file' , $ten_octets_string);
        $this->assertFalse($donneesFormulaire->isValidable());
        $this->assertSame(
            'L\'ensemble des fichiers dépasse le poids limite autorisé : 100 octets, (110 trouvé)',
            $donneesFormulaire->getLastError()
        );
    }

    /**
     * @throws Exception
     */
    public function testThresholdSizeWithFields() {
        $donneesFormulaire = $this->getCustomDonneesFormulaire(__DIR__ . '/../fixtures/file-limit.yml');
        $ten_octets_string = '0123456789';
        for ($i = 0; $i < 20; ++$i) {
            $donneesFormulaire->addFileFromData('multiple_file', 'file_' . $i, $ten_octets_string, $i);
        }
        $this->assertTrue($donneesFormulaire->isValidable());

        for ($i = 0; $i < 10; ++$i) {
            $donneesFormulaire->addFileFromData('multiple_file_10_octets_per_file', 'file_' . $i, $ten_octets_string, $i);
        }
        $donneesFormulaire->addFileFromData('file_10_octets', 'file' , $ten_octets_string);
        $this->assertFalse($donneesFormulaire->isValidable());
        $this->assertSame(
            'L\'ensemble des fichiers dépasse le poids limite autorisé : 100 octets, (110 trouvé)',
            $donneesFormulaire->getLastError()
        );
    }

    /**
     * @throws DonneesFormulaireException
     */
    public function testGetFileSizeException() {
        $this->expectException(DonneesFormulaireException::class);
        $this->expectExceptionMessage("Le fichier 50 du champ «multiple_file» (vfs://test/workspace//YZZT.yml_multiple_file_50) n'existe pas.");
        $donneesFormulaire = $this->getCustomDonneesFormulaire(__DIR__ . '/../fixtures/file-limit.yml');

        $donneesFormulaire->getFileSize($donneesFormulaire->getFieldData('multiple_file')->getField(), 50);
    }
}