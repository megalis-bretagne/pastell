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

}