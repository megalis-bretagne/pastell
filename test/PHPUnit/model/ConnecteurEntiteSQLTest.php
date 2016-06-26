<?php

class ConnecteurEntiteSQLTest extends PastellTestCase {

	/**
	 * 
	 * @return ConnecteurEntiteSQL
	 */
	private function getConnecteurEntiteSQL(){
		$sqlQuery = $this->getObjectInstancier()->SQLQuery;
		return new ConnecteurEntiteSQL($sqlQuery);
	}

	public function testGetAll(){
		$result = $this->getConnecteurEntiteSQL()->getAll(1);
		$this->assertEquals("Fake GED",$result[1]['libelle']);
	}

	public function testGetAllLocal(){
		$result = $this->getConnecteurEntiteSQL()->getAllLocal();
		$this->assertEquals("Fake GED",$result[1]['libelle']);
	}
	
	public function testAddConnecteu(){
		$id_ce = $this->getConnecteurEntiteSQL()->addConnecteur(1, 'mailsec', 'mailsec', 'Mail sécurisé');
		$this->assertEquals(13, $id_ce);
	}

	public function testGetInfo(){
		$result = $this->getConnecteurEntiteSQL()->getInfo(1);
		$this->assertEquals('Fake iParapheur', $result['libelle']);
	}
	
	public function testDelete(){
		$this->getConnecteurEntiteSQL()->delete(1);
		$this->assertEquals(10, count($this->getConnecteurEntiteSQL()->getAll(1)));
	}
	
	public function testEdit(){
		$new_libelle = "***test***";
		$this->getConnecteurEntiteSQL()->edit(1, $new_libelle);
		$result = $this->getConnecteurEntiteSQL()->getInfo(1);
		$this->assertEquals($new_libelle, $result['libelle']);
	}
	
	public function testGetDisponible(){
		$result = $this->getConnecteurEntiteSQL()->getDisponible(1, 'signature');
		$this->assertEquals('Fake iParapheur', $result[0]['libelle']);
	}
	
	public function testGetGlobal(){
		$result = $this->getConnecteurEntiteSQL()->getGlobal('horodateur-interne');
		$this->assertEquals(10, $result);
	}

	public function testGetDisponibleUsed(){
		$info = $this->getConnecteurEntiteSQL()->getDisponibleUsed(1,'mailsec');
		$this->assertEquals("Mail securise",$info[0]['libelle']);
	}

	public function testGetDisponibleLocalUsed(){
		$info = $this->getConnecteurEntiteSQL()->getDisponibleUsedLocal("mailsec");
		$this->assertEquals("Mail securise",$info[0]['libelle']);
	}
	
	public function testGetOne(){
		$result = $this->getConnecteurEntiteSQL()->getOne('fakeIparapheur');
		$this->assertEquals(1, $result);
	}
	
	public function testGetAllById(){
		$result = $this->getConnecteurEntiteSQL()->getAllById('fakeIparapheur');
		$this->assertEquals('Fake iParapheur', $result[0]['libelle']);
	}
	
	public function testGetByType(){
		$result = $this->getConnecteurEntiteSQL()->getByType(1, 'signature');
		$this->assertEquals('Fake iParapheur', $result[0]['libelle']);		
	}
	
	public function testGetAllId(){
		$result = $this->getConnecteurEntiteSQL()->getAllId();
		$this->assertEquals('fakeIparapheur', $result[0]['id_connecteur']);
	}
	
	public function testListNotUsed(){
		$result = $this->getConnecteurEntiteSQL()->listNotUsed(1);
		$this->assertEquals('SEDA CG86', $result[0]['libelle']);
	}
	
	public function testListNotUsedGlobal(){
		$result = $this->getConnecteurEntiteSQL()->listNotUsed(0);
		$this->assertEquals('SEDA CG86', $result[0]['libelle']);
	}

	public function testGetAllUsed(){
		$info = $this->getConnecteurEntiteSQL()->getAllUsed();
		$this->assertEquals(12,count($info));
	}
}
