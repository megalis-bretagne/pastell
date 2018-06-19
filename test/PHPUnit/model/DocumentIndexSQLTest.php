<?php

class DocumentIndexSQLTest extends PastellTestCase {

	/**
	 * @var DocumentIndexSQL
	 */
	private $documentIndexSQL;

	protected function setUp() {
		parent::setUp();
		$this->documentIndexSQL = $this->getObjectInstancier()->getInstance(DocumentIndexSQL::class);
		$this->documentIndexSQL->index('FOO',"bar","baz");
	}

	public function testIndex(){
		$this->assertEquals("baz",$this->documentIndexSQL->get('FOO',"bar"));
	}

	public function testReIndex(){
		$this->documentIndexSQL->index('FOO',"bar","baz2");
		$this->assertEquals("baz2",$this->documentIndexSQL->get('FOO',"bar"));
	}

	public function testGetByFieldValue(){
		$this->assertEquals("FOO",$this->documentIndexSQL->getByFieldValue("bar","baz"));
	}

}