<?php

class ConnecteurDisponibleTest extends PastellTestCase {

	public function getDataSet() {
		return new PHPUnit_Extensions_Database_DataSet_YamlDataSet( __DIR__."/connecteur_disponible_database.yml");
	}

	/**
	 * @return ConnecteurDisponible
	 */
	private function getConnecteurDisponible(){
		return $this->getObjectInstancier()->getInstance('ConnecteurDisponible');
	}
	
	public function testGetConnecteurDisponible(){
		$result = $this->getConnecteurDisponible()->getList(1,1,'mailsec');
		$this->assertEquals("Mail Bourg-en-Bresse",$result[0]['libelle']);
	}
	
	public function testGetConnecteurDisponibleNoRight(){
		$this->assertEmpty($this->getConnecteurDisponible()->getList(2,1,'mailsec'));
	}
	
	public function testGetConnecteurDisponibleInherited(){
		$result = $this->getConnecteurDisponible()->getList(1,2,'mailsec');
		$this->assertEquals("Mail Bourg-en-Bresse",$result[0]['libelle']);
	}
	
}
