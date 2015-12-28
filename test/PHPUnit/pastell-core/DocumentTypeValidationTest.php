<?php

class DocumentTypeValidationTest extends PHPUnit_Framework_TestCase {

	/** @var  DocumentTypeValidation */
	private $documentTypeValidation;


	protected function setUp(){
		$this->documentTypeValidation =  new DocumentTypeValidation(new YMLLoader(new MemoryCacheNone()));
		$this->documentTypeValidation->setConnecteurTypeList(array('mailsec'));
		$this->documentTypeValidation->setActionClassList(array('Supprimer','EnvoyerMailSec','MailSecRenvoyer'));
		$this->documentTypeValidation->setEntiteTypeList(array());
	}

	public function testValidate(){
		$this->assertTrue($this->documentTypeValidation->validate(PASTELL_PATH."/module/mailsec/definition.yml"));
	}

	public function testGetModuleDefinition(){
		$this->assertNotEmpty($this->documentTypeValidation->getModuleDefinition());
	}

	public function testGetLastError(){
		$this->assertFalse($this->documentTypeValidation->validate(""));
		$this->assertEquals("Fichier definition.yml absent",$this->documentTypeValidation->getLastError()[0]);
	}



}