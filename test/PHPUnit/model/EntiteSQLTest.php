<?php

class EntiteSQLTest extends PastellTestCase {

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

	public function testGetEntiteFromData(){
		$info = $this->getEntiteSQL()->getEntiteFromData(array('id_e'=>1));
		$this->assertEquals("Bourg-en-Bresse",$info['denomination']);
	}

	public function testGetEntiteFromDataFromDenomination(){
		$info = $this->getEntiteSQL()->getEntiteFromData(array('denomination'=>"Bourg-en-Bresse"));
		$this->assertEquals(1,$info['id_e']);
	}

	public function testGetEntiteFromDataIdNotExisting(){
		$this->setExpectedException("Exception","L'identifiant de l'entite n'existe pas : {id_e=42}");
		$this->getEntiteSQL()->getEntiteFromData(array('id_e'=>42));
	}

	public function testGetEntiteFromDataDenominationNotExisting(){
		$this->setExpectedException("Exception","La dénomination de l'entité n'existe pas : {denomination=FizzBuzz}");
		$this->getEntiteSQL()->getEntiteFromData(array('denomination'=>"FizzBuzz"));
	}

	public function testGetEntiteFromDataFailed(){
		$this->setExpectedException("Exception","Aucun paramètre permettant la recherche de l'entité n'a été renseigné");
		$this->getEntiteSQL()->getEntiteFromData(array());
	}

	public function testGetEntiteFromDataSameDenomination(){
		$sql = "INSERT INTO entite(denomination,siren) VALUES ('Bourg-en-Bresse','123456789')";
		$this->getSQLQuery()->query($sql);
		$this->setExpectedException("Exception","Plusieurs entités portent le même nom, préférez utiliser son identifiant");
		$this->getEntiteSQL()->getEntiteFromData(array('denomination'=>"Bourg-en-Bresse"));
	}


}
