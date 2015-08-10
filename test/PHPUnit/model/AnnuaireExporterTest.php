<?php 

require_once __DIR__.'/../init.php';

class AnnuaireExporterTest extends PastellTestCase {
	
	public function reinitDatabaseOnSetup(){
		return true;
	}
	
	private function getAnnuaireSQL(){
		$sqlQuery = $this->getObjectInstancier()->SQLQuery;
		return new AnnuaireSQL($sqlQuery);
	}
	
	private function getAnnuaireGroupsSQL(){
		return new AnnuaireGroupe($this->getObjectInstancier()->SQLQuery, 1);
	}

	private function getCSVContent(){
		$testStream = org\bovigo\vfs\vfsStream::setup('test');
		$testStreamUrl = org\bovigo\vfs\vfsStream::url('test');
		$fileURL = $testStreamUrl."/annuaire.csv";
	
	
		$csvOutput = new CSVoutput();
		$csvOutput->disableHeader();
		$csvOutput->setOutputFile($fileURL);
		$annuaireExporter = new AnnuaireExporter($csvOutput, $this->getAnnuaireSQL(), $this->getAnnuaireGroupsSQL());
		$annuaireExporter->export(1);
	
		return file_get_contents($fileURL);
	}
	
	public function testVide(){
		$this->assertEmpty($this->getCSVContent());
	}
	
	public function testOne(){
		$this->getAnnuaireSQL()->add(1,"Eric Pommateau","eric@sigmalis.com");
		$this->assertEquals("eric@sigmalis.com,\"Eric Pommateau\"\n", $this->getCSVContent());
	}
	
	public function testTwo(){
		$this->getAnnuaireSQL()->add(1,"Eric Pommateau","eric@sigmalis.com");
		$this->getAnnuaireSQL()->add(1,"Toto","toto@sigmalis.com");
		$this->assertEquals("eric@sigmalis.com,\"Eric Pommateau\"\ntoto@sigmalis.com,Toto\n", $this->getCSVContent());
	}

	public function testGroupe(){
		$id_a = $this->getAnnuaireSQL()->add(1,"Eric Pommateau","eric@sigmalis.com");
		$this->getAnnuaireGroupsSQL()->addToGroupe(2, $id_a);
		$this->assertEquals("eric@sigmalis.com,\"Eric Pommateau\",Elu\n", $this->getCSVContent());
	}
	
	public function test2Groupe(){
		$id_a = $this->getAnnuaireSQL()->add(1,"Eric Pommateau","eric@sigmalis.com");
		$this->getAnnuaireGroupsSQL()->addToGroupe(1, $id_a);
		$this->getAnnuaireGroupsSQL()->addToGroupe(2, $id_a);
		$this->assertEquals("eric@sigmalis.com,\"Eric Pommateau\",Elu,\"Mon groupe\"\n", $this->getCSVContent());
	}
	
	public function testError(){
		$this->assertTrue(false);
	}
	
}