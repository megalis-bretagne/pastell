<?php

require_once __DIR__.'/../init.php';


class DonneesFormulaireTest extends PastellTestCase {

	public function reinitFileSystemOnSetup(){
		return true;
	}
	
	public function reinitDatabaseOnSetup(){
		return true;
	}

	/**
	 * @return DonneesFormulaire
	 */
	private function getDonneesFormulaire(){
		return $this->getObjectInstancier()->DonneesFormulaireFactory->get('toto','test');
	}
	
	/**
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
	
	private function getDonneesFormulaireChampsCache(){
		$filePath = $this->getObjectInstancier()->workspacePath."/YZZT.yml";
		$ymlLoader = new YMLLoader();
		$module_definition = $ymlLoader->getArray(__DIR__."/../fixtures/definition-champs-cache.yml");
		$documentType = new DocumentType("test-fichier", $module_definition);
		return new DonneesFormulaire($filePath, $documentType);
	}
	
	public function testModifOngletCache(){
		$donneesFormulaire = $this->getDonneesFormulaireChampsCache();
		$donneesFormulaire->setData("chaine","12");
		$this->assertEquals("12",$donneesFormulaire->get("chaine"));
	}
	
	public function testModifOngletCacheFichier(){
		$donneesFormulaire = $this->getDonneesFormulaireChampsCache();
		$donneesFormulaire->addFileFromData("fichier_visible", "test.txt", "texte");
		$this->assertEquals("texte",$donneesFormulaire->getFileContent("fichier_visible"));
	}
	
	public function testSaveAllFile(){
		$donneesFormulaire = $this->getDonneesFormulaireChampsCache();
		$donneesFormulaire->setData("chaine","12");
		
		$file_path = $this->getObjectInstancier()->workspacePath."/test.txt";
		file_put_contents($file_path, "texte");
		
		$files = array('fichier_visible'=>array('tmp_name'=>$file_path,'error'=>UPLOAD_ERR_OK,'name'=>'test.txt'));
		
		$fileUploader = new FileUploader();
		$fileUploader->setFiles($files);
		$fileUploader->setUnitTesting(true);
		$donneesFormulaire->saveAllFile($fileUploader);
		$this->assertEquals("texte",$donneesFormulaire->getFileContent('fichier_visible'));
	}
	
	public function testSaveAllFileHidden(){
		$donneesFormulaire = $this->getDonneesFormulaireChampsCache();
		$donneesFormulaire->setData("chaine","12");
	
		$file_path = $this->getObjectInstancier()->workspacePath."/test.txt";
		file_put_contents($file_path, "texte");
	
		$files = array('fichier_hidden'=>array('tmp_name'=>$file_path,'error'=>UPLOAD_ERR_OK,'name'=>'test.txt'));
	
		$fileUploader = new FileUploader();
		$fileUploader->setFiles($files);
		$fileUploader->setUnitTesting(true);
		$donneesFormulaire->saveAllFile($fileUploader);
		$this->assertEquals("texte",$donneesFormulaire->getFileContent('fichier_hidden'));
	}

}