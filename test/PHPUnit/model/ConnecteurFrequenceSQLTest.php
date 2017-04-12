<?php

class ConnecteurFrequenceSQLTest extends PastellTestCase {

	/** @var  ConnecteurFrequenceSQL */
	private $connecteurFrequenceSQL;

	protected function setUp() {
		parent::setUp();
		$this->connecteurFrequenceSQL = $this->getObjectInstancier()->getInstance("ConnecteurFrequenceSQL");
	}

	private function create(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->id_ce = 1;
		return $this->connecteurFrequenceSQL->edit($connecteurFrequence);
	}

	public function testCreate(){
		$id_cf = $this->create();
		$connecteurFrequence = $this->connecteurFrequenceSQL->getConnecteurFrequence($id_cf);
		$this->assertEquals(1,$connecteurFrequence->id_cf);
	}

	public function testUpdate(){
		$id_cf = $this->create();
		$connecteurFrequence = $this->connecteurFrequenceSQL->getConnecteurFrequence($id_cf);
		$connecteurFrequence->type_connecteur = ConnecteurFrequence::TYPE_GLOBAL;
		$this->connecteurFrequenceSQL->edit($connecteurFrequence);
		$connecteurFrequence = $this->connecteurFrequenceSQL->getConnecteurFrequence($id_cf);
		$this->assertEquals(ConnecteurFrequence::TYPE_GLOBAL,$connecteurFrequence->type_connecteur);
	}

	public function testDelete(){
		$id_cf = $this->create();
		$this->connecteurFrequenceSQL->delete($id_cf);
		$this->assertNull($this->connecteurFrequenceSQL->getConnecteurFrequence($id_cf));
	}

	public function testGetAll(){
		$this->create();
		$all = $this->connecteurFrequenceSQL->getAll();
		$this->assertEquals(1,$all[0]->id_cf);
	}

}