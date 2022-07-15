<?php

class EntiteFluxAPIControllerTest extends PastellTestCase
{
    private function associateConnecteur()
    {
        return $this->getInternalAPI()->post("/entite/1/flux/test/connecteur/12", ["type" => "test"]);
    }

    public function testAssociateConnecteur()
    {
        $info = $this->associateConnecteur();
        $this->assertNotEmpty($info['id_fe']);
        $this->assertIsString($info['id_fe']);
    }

    public function testDoActionAction()
    {
        $this->associateConnecteur();
        $result = $this->getInternalAPI()->post("/entite/1/flux/test/action", ["type" => "test","id_ce" => 12,"flux" => 'test',"action" => "ok"]);
        $this->assertEquals("OK !", $result['message']);
    }

    public function testDeleteFluxConnecteurAction()
    {
        $info_before = $this->getInternalAPI()->get("/entite/1/flux");
        $this->getInternalAPI()->delete("/entite/1/flux/test?id_fe=1");
        $info_after = $this->getInternalAPI()->get("/entite/1/flux");
        $this->assertCount(count($info_before) - 1, $info_after);
    }

    public function testDeleteFluxConnecteurNotExist()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le connecteur-flux n'existe pas : {id_fe=42}");
        $this->getInternalAPI()->delete("/entite/1/flux/test?id_fe=42");
    }

    public function testDeleteFluxConnecteurNotExistForEntity()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le connecteur-flux n'existe pas sur l'entité spécifié : {id_fe=1, id_e=2}");
        $this->getInternalAPI()->delete("/entite/2/flux/test?id_fe=1");
    }

    public function testDoActionNotExist()
    {
        $this->associateConnecteur();
        $this->expectException("Exception");
        $this->expectExceptionMessage("L'action foo n'existe pas.");
        $this->getInternalAPI()->post("/entite/1/flux/test/action", ["type" => "test","id_ce" => 12,"flux" => 'test',"action" => "foo"]);
    }

    public function testDoActionFail()
    {
        $this->associateConnecteur();
        $this->expectException("Exception");
        $this->expectExceptionMessage("Fail !");
        $this->getInternalAPI()->post("/entite/1/flux/test/action", ["type" => "test","id_ce" => 12,"flux" => 'test',"action" => "fail"]);
    }

    public function testDoActionNotPossible()
    {
        $this->associateConnecteur();
        $this->expectException("Exception");
        $this->expectExceptionMessage("L'action « not_possible »  n'est pas permise : automatique n'est pas vérifiée");
        $this->getInternalAPI()->post("/entite/1/flux/test/action", ["type" => "test","id_ce" => 12,"flux" => 'test',"action" => "not_possible"]);
    }

    public function testDoActionNoConnecteur()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le connecteur de type SAE n'existe pas pour le flux test.");
        $this->getInternalAPI()->post("/entite/1/flux/test/action", ["type" => "SAE","id_ce" => 12,"flux" => 'test',"action" => "ok"]);
    }

    public function testDoPostTwoSameType()
    {
        $connecteur_sae = $this->createConnector("as@lae-rest", "TEST SAE");
        $this->associateFluxWithConnector($connecteur_sae['id_ce'], 'test', 'SAE', PastellTestCase::ID_E_COL, 0);
        $this->associateFluxWithConnector(12, 'test', 'test', PastellTestCase::ID_E_COL, 0);
        $connecteur_2 = $this->createConnector("test", "TEST 2");
        $this->associateFluxWithConnector($connecteur_2['id_ce'], 'test', 'test', PastellTestCase::ID_E_COL, 1);

        $result = $this->getInternalAPI()->get("/entite/1/flux", ['flux' => 'test']);
        $this->assertEquals(12, $result[1]['id_ce']);
        $this->assertEquals($connecteur_2['id_ce'], $result[2]['id_ce']);
        $this->assertEquals(0, $result[1]['num_same_type']);
        $this->assertEquals(1, $result[2]['num_same_type']);
    }
}
