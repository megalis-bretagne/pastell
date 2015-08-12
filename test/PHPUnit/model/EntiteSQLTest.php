<?php

require_once __DIR__.'/../init.php';

class EntiteSQLTest extends PastellTestCase {

	public function reinitDatabaseOnSetup(){
		return true;
	}

	private function getEntiteSQL(){
		$sqlQuery = $this->getObjectInstancier()->SQLQuery;
		return new EntiteSQL($sqlQuery);
	}

	public function testGetDemominationEntiteRacine(){
		$this->assertEquals(EntiteSQL::ENTITE_RACINE_DENOMINATION,$this->getEntiteSQL()->getDenomination(0));
	}
	
	public function testGetDenomination(){
		$this->assertEquals("Bourg-en-Bresse",$this->getEntiteSQL()->getDenomination(1));
	}

	public function testGetDenominationEmpty(){
		$this->assertEquals("",$this->getEntiteSQL()->getDenomination(42));
	}

	public function testGetEntiteMere(){
		$id_e = $this->getEntiteSQL()->getEntiteMere(2);
		$this->assertEquals(1, $id_e);
	}
	
}
