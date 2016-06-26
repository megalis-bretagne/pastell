<?php

class JournalAPIControllerTest extends PastellTestCase {

	/** @var  JournalAPIController */
	private $journalAPIController;

	protected function setUp() {
		parent::setUp();
		$this->journalAPIController = $this->getAPIController('Journal',1);
		$this->getJournal()->add(Journal::TEST,0,'','test',"Test");
	}

	public function testList(){
		$info = $this->journalAPIController->listAction();
		$this->assertEquals('Test',$info[0]['message']);
	}

	public function testCSV(){
		$this->journalAPIController->setRequestInfo(array('format'=>'csv','csv_entete_colonne'=>true));
		$this->setExpectedException("Exception","Exit called with code 0");
		$this->expectOutputRegex("#Test#");
		$this->journalAPIController->listAction();
	}

	public function testNotAuthorized(){
		$this->journalAPIController->setRequestInfo(array('id_e'=>42));
		$this->setExpectedException("Exception","Acces interdit id_e=42, id_d=,id_u=1,type=");
		$this->journalAPIController->listAction();
	}

	public function testPreuve(){
		$this->journalAPIController->setRequestInfo(array('id_j'=>1));
		$this->setExpectedException("Exception","Exit called with code 0");
		$this->expectOutputRegex("#pastell-journal-preuve-1.tsr#");
		$this->journalAPIController->preuveAction();
	}

	public function testPreuveFailed(){
		$this->journalAPIController->setRequestInfo(array('id_j'=>42));
		$this->setExpectedException("Exception","Accès interdit {id_j=42}");
		$this->journalAPIController->preuveAction();
	}
}