<?php

class AgentSQLTest extends PastellTestCase
{
    private function getAgentSQL()
    {
        $sqlQuery = $this->getObjectInstancier()->SQLQuery;
        return new AgentSQL($sqlQuery);
    }

    private function getInfo()
    {
        return array("007","M.","Bond","Bond","James","XYZ","Agent secret","UK","Royaume-Uni de Grande Bretagne et d'Irlande du Nord","123456789","2","Libelle","42","43");
    }

    private function getAgentSQLWithInfo()
    {
        $agentSQL = $this->getAgentSQL();
        $agentSQL->add($this->getInfo());
        return $agentSQL;
    }

    public function testNothing()
    {
        $this->assertTrue(true);
    }

    public function testAdd()
    {
        $agentSQL = $this->getAgentSQLWithInfo();
        $this->assertEquals(1, $agentSQL->getNbAgent("123456789"));
    }

    public function testAddSirenCol()
    {
        $info_collectivite = array("siren" => "444444444");
        $this->getAgentSQL()->add($this->getInfo(), $info_collectivite);
        $this->assertEquals(0, $this->getAgentSQL()->getNbAgent("123456789"));
        $this->assertEquals(1, $this->getAgentSQL()->getNbAgent("444444444"));
    }

    public function testGetBySiren()
    {
        $agentSQL = $this->getAgentSQLWithInfo();
        $result = $agentSQL->getBySiren("123456789", 0);
        $this->assertEquals("Bond", $result[0]['nom_usage']);
    }

    public function testGetInfo()
    {
        $agentSQL = $this->getAgentSQLWithInfo();
        $result = $agentSQL->getInfo(1, "123456789");
        $this->assertEquals("Bond", $result['nom_usage']);
    }

    public function testNbAllAgent()
    {
        $agentSQL = $this->getAgentSQLWithInfo();
        $nb_agent = $agentSQL->getNbAllAgent("Bond");
        $this->assertEquals(1, $nb_agent);
    }

    public function testgetAllAgent()
    {
        $agentSQL = $this->getAgentSQLWithInfo();
        $result = $agentSQL->getAllAgent("Bond", 0);
        $this->assertEquals("Bond", $result[0]['nom_usage']);
    }

    public function testGetAll()
    {
        $agentSQL = $this->getAgentSQLWithInfo();
        $result = $agentSQL->getAll("123456789");
        $this->assertEquals("Bond", $result[0]['nom_usage']);
    }

    public function testClean()
    {
        $agentSQL = $this->getAgentSQLWithInfo();
        $this->assertEquals(1, $this->getAgentSQL()->getNbAgent("123456789"));
        $agentSQL->clean("123456789");
        $this->assertEquals(0, $this->getAgentSQL()->getNbAgent("123456789"));
    }
}
