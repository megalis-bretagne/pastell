<?php

class ConnecteurFrequenceSQLTest extends PastellTestCase {

	/** @var  ConnecteurFrequenceSQL */
	private $connecteurFrequenceSQL;

	/** @var  ConnecteurFrequence */
	private $connecteurFrequence;

	private $id_cf;

	protected function setUp() {
		parent::setUp();
		$this->connecteurFrequenceSQL = $this->getObjectInstancier()->getInstance("ConnecteurFrequenceSQL");
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->id_ce = 1;
		$this->id_cf = $this->connecteurFrequenceSQL->edit($connecteurFrequence);
		$this->connecteurFrequence = $this->connecteurFrequenceSQL->getConnecteurFrequence($this->id_cf);
	}

	public function testCreate(){
		$this->assertEquals(1,$this->connecteurFrequence->id_cf);
	}

	public function testUpdate(){
		$this->connecteurFrequence->type_connecteur = ConnecteurFrequence::TYPE_GLOBAL;
		$this->connecteurFrequenceSQL->edit($this->connecteurFrequence);
		$connecteurFrequence = $this->connecteurFrequenceSQL->getConnecteurFrequence($this->id_cf);
		$this->assertEquals(ConnecteurFrequence::TYPE_GLOBAL,$connecteurFrequence->type_connecteur);
	}

	public function testDelete(){
		$this->connecteurFrequenceSQL->delete($this->id_cf);
		$this->assertNull($this->connecteurFrequenceSQL->getConnecteurFrequence($this->id_cf));
	}

	public function testGetAll(){
		$all = $this->connecteurFrequenceSQL->getAll();
		$this->assertEquals(1,$all[0]->id_cf);
	}

}