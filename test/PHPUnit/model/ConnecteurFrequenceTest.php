<?php

class ConnecteurFrequenceTest extends PastellTestCase {

	/** @var  ConnecteurFrequenceSQL */
	private $connecteurFrequenceSQL;

	protected function setUp() {
		parent::setUp();
		$this->connecteurFrequenceSQL = $this->getObjectInstancier()->getInstance("ConnecteurFrequenceSQL");
	}

	public function testCreate(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->id_ce = 1;

		$id_cf = $this->connecteurFrequenceSQL->create($connecteurFrequence);

		$result = $this->connecteurFrequenceSQL->getInfo($id_cf);
		$this->assertEquals(1,$result['id_cf']);


	}




}