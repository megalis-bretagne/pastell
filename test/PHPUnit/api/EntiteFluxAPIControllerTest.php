<?php

class EntiteFluxAPIControllerTest extends PastellTestCase {

	private function associateConnecteur(){
		return $this->getInternalAPI()->post("/entite/1/flux/test/connecteur/12",array("type"=>"test"));
	}

	public function testAssociateConnecteur(){
		$info = $this->associateConnecteur();
		$this->assertNotEmpty($info['id_fe']);
	}

	public function testDoActionAction(){
		$this->associateConnecteur();
		$result = $this->getInternalAPI()->post("/entite/1/flux/test/action",array("type"=>"test","id_ce"=>12,"flux"=>'test',"action"=>"ok"));
		$this->assertEquals("OK !",$result['message']);
	}

	public function testDeleteFluxConnecteurAction(){
		$info_before = $this->getInternalAPI()->get("/entite/1/flux");
		$this->getInternalAPI()->delete("/entite/1/flux/test?id_fe=1");
		$info_after = $this->getInternalAPI()->get("/entite/1/flux");
		$this->assertEquals(count($info_before) - 1,count($info_after));
	}

	public function testDeleteFluxConnecteurNotExist(){
		$this->setExpectedException("Exception","Le connecteur-flux n'existe pas : {id_fe=42}");
		$this->getInternalAPI()->delete("/entite/1/flux/test?id_fe=42");
	}

	public function testDeleteFluxConnecteurNotExistForEntity(){
		$this->setExpectedException("Exception","Le connecteur-flux n'existe pas sur l'entité spécifié : {id_fe=1, id_e=2}");
		$this->getInternalAPI()->delete("/entite/2/flux/test?id_fe=1");
	}

	public function testDoActionNotExist(){
		$this->associateConnecteur();
		$this->setExpectedException("Exception","L'action foo n'existe pas.");
		$this->getInternalAPI()->post("/entite/1/flux/test/action",array("type"=>"test","id_ce"=>12,"flux"=>'test',"action"=>"foo"));
	}

	public function testDoActionFail(){
		$this->associateConnecteur();
		$this->setExpectedException("Exception","Fail !");
		$this->getInternalAPI()->post("/entite/1/flux/test/action",array("type"=>"test","id_ce"=>12,"flux"=>'test',"action"=>"fail"));
	}

	public function testDoActionNotPossible(){
		$this->associateConnecteur();
		$this->setExpectedException("Exception","L'action « not_possible »  n'est pas permise : role_id_e n'est pas vérifiée");
		$this->getInternalAPI()->post("/entite/1/flux/test/action",array("type"=>"test","id_ce"=>12,"flux"=>'test',"action"=>"not_possible"));
	}

	public function testDoActionNoConnecteur(){
		$this->setExpectedException("Exception","Le connecteur de type SAE n'existe pas pour le flux test.");
		$this->getInternalAPI()->post("/entite/1/flux/test/action",array("type"=>"SAE","id_ce"=>12,"flux"=>'test',"action"=>"ok"));
	}

	public function testDoPostTwoSameType(){
		$connecteur_sae = $this->createConnector("as@lae-rest","TEST SAE");
		$this->getInternalAPI()->post("/entite/1/flux/test/connecteur/{$connecteur_sae['id_ce']}",array("type"=>"SAE","num_same_type"=>0));

		$this->getInternalAPI()->post("/entite/1/flux/test/connecteur/12",array("type"=>"test","num_same_type"=>0));
		$connecteur_2 = $this->createConnector("test","TEST 2");
		$this->getInternalAPI()->post("/entite/1/flux/test/connecteur/{$connecteur_2['id_ce']}",array("type"=>"test","num_same_type"=>1));

		$result = $this->getInternalAPI()->get("/entite/1/flux",['flux'=>'test']);
		$this->assertEquals(12,$result[1]['id_ce']);
		$this->assertEquals($connecteur_2['id_ce'],$result[2]['id_ce']);
		$this->assertEquals(0,$result[1]['num_same_type']);
		$this->assertEquals(1,$result[2]['num_same_type']);
	}


}