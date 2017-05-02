<?php

require_once(PASTELL_PATH."/lib/dbupdate/DatabaseUpdate.class.php");

class DatabaseUpdateTest extends PastellTestCase {


	public function testCreateSQL(){
		$databaseUpdate = new DatabaseUpdate(file_get_contents(PASTELL_PATH."/installation/pastell.bin"),$this->getSQLQuery());
		$this->assertTrue(is_array($databaseUpdate->getAllSQLCommand()));
	}

	public function testFileEmpty(){
		$databaseUpdate = new DatabaseUpdate(false,$this->getSQLQuery());
		$this->assertTrue(is_array($databaseUpdate->getAllSQLCommand()));
	}

	public function testGetJson(){
		$databaseUpdate = new DatabaseUpdate(false,$this->getSQLQuery());
		$json = $databaseUpdate->getDatabaseDefinition();
		$result = json_decode($json,true);
		$this->assertTrue(is_array($result));
	}

	public function testWrite(){
		$databaseUpdate = new DatabaseUpdate(false,$this->getSQLQuery());
		$databaseUpdate->writeDefinition($this->getObjectInstancier()->{'workspacePath'}."/toto.bin",$this->getObjectInstancier()->{'workspacePath'}."/toto.sql");
		$content = file_get_contents($this->getObjectInstancier()->{'workspacePath'}."/toto.bin");
		$result = json_decode($content,true);
		$this->assertTrue(is_array($result));
	}

	public function testGetDiff(){
		$databaseUpdate = new DatabaseUpdate(false,$this->getSQLQuery());
		$diff = $databaseUpdate->getDiff();
		$this->assertTrue(is_array($diff));
	}

	public function testMajDatabase(){
		/** @var SQLQuery $sqlQuery */
		$sqlQuery = $this->getMockBuilder("SQLQuery")->disableOriginalConstructor()->getMock();
		$databaseUpdate = new DatabaseUpdate(false,$this->getSQLQuery());
		$databaseUpdate->majDatabase($sqlQuery, function ($log){});
	}

}