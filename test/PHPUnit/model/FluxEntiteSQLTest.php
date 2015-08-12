<?php

require_once __DIR__.'/../init.php';

class FluxEntiteSQLTest extends PastellTestCase {

	public function reinitDatabaseOnSetup(){
		return true;
	}

	/**
	 * 
	 * @return FluxEntiteSQL
	 */
	private function getFluxEntiteSQL(){
		$sqlQuery = $this->getObjectInstancier()->SQLQuery;
		return new FluxEntiteSQL($sqlQuery);
	}
	
	public function testGetConnecteur(){
		$connecteur = $this->getFluxEntiteSQL()->getConnecteur(1, 'actes-generique', 'signature');
		$this->assertEquals("Fake iParapheur",$connecteur['libelle']);
	}
	
	public function testGetConnecteurGlobal(){
		$connecteur = $this->getFluxEntiteSQL()->getConnecteur(0, 'global', 'horodateur');
		$this->assertEquals("horodatage",$connecteur['libelle']);
	}
	
	public function testgetConnecteurId(){
		$id_ce = $this->getFluxEntiteSQL()->getConnecteurId(1, 'actes-generique', 'signature');
		$this->assertEquals(1,$id_ce);
	}
	
	public function testGetConnecteurById(){
		$connecteur = $this->getFluxEntiteSQL()->getConnecteurById(1);
		$this->assertEquals(1,$connecteur['id_ce']);		
	}

	public function testGetAll(){
		$result = $this->getFluxEntiteSQL()->getAll(1);
		$this->assertEquals("Fake iParapheur",$result['actes-generique']['signature']['libelle']);
	}
	
	public function testGetAllFluxEntite(){
		$result = $this->getFluxEntiteSQL()->getAllFluxEntite(1);
		$this->assertEquals(1,$result[0]['id_ce']);
	}
	
	public function testGetAllFluxEntiteWithFlux(){
		$result = $this->getFluxEntiteSQL()->getAllFluxEntite(1,'actes-generique');
		$this->assertEquals(1,$result[0]['id_ce']);
	}
	
	public function testGetAllFluxEntiteWithType(){
		$result = $this->getFluxEntiteSQL()->getAllFluxEntite(1,false,'signature');
		$this->assertEquals(1,$result[0]['id_ce']);
	}
	
	public function testGetAllFluxEntiteWithTypeAndFlux(){
		$result = $this->getFluxEntiteSQL()->getAllFluxEntite(1,'actes-generique','signature');
		$this->assertEquals(1,$result[0]['id_ce']);
	}
	
	public function testAddConnecteur(){
		$id_fe = $this->getFluxEntiteSQL()->addConnecteur(1, 'mailsec', 'mailsec', 12);
		$this->assertEquals(9,$id_fe);
	}
	
	public function testDeleteConnecteur(){
		$this->getFluxEntiteSQL()->deleteConnecteur(1, 'actes-generique', 'signature');
		$connecteur = $this->getFluxEntiteSQL()->getConnecteur(1, 'actes-generique', 'signature');
		$this->assertEmpty($connecteur);
	}
	
	public function testRemoveConnecteur(){
		$this->getFluxEntiteSQL()->removeConnecteur(1);
		$connecteur = $this->getFluxEntiteSQL()->getConnecteur(1, 'actes-generique', 'signature');
		$this->assertEmpty($connecteur);
	}
	
	public function testIsUsed(){
		$this->assertNotEmpty($this->getFluxEntiteSQL()->isUsed(1));
	}
}
