<?php

class EntiteListeTest extends PastellTestCase
{
    /** @var  EntiteListe */
    private $entiteListe;

    protected function setUp()
    {
        parent::setUp();
        $this->entiteListe = new EntiteListe($this->getSQLQuery());
    }

    public function testCountCollectivite()
    {
        $this->assertEquals(1, $this->entiteListe->countCollectivite());
    }

    public function testGetAllCollectivite()
    {
        $this->assertEquals(
            "Bourg-en-Bresse",
            $this->entiteListe->getAllCollectivite(0, "")[0]['denomination']
        );
    }

    public function testGetAll()
    {
        $this->entiteListe->setFiltre("Bourg-en-Bresse");
        $this->assertEquals(
            "Bourg-en-Bresse",
            $this->entiteListe->getAll("collectivite")[0]['denomination']
        );
    }

    public function testWithoutRecherche()
    {
        $this->entiteListe->setFiltre(false);
        $this->assertEquals(
            "Bourg-en-Bresse",
            $this->entiteListe->getAll("collectivite")[0]['denomination']
        );
    }


    public function testGetNbCollectivite()
    {
        $this->assertEquals(1, $this->entiteListe->getNbCollectivite("Bourg-en-Bresse"));
    }

    public function testGetInfoFromArray()
    {
        $this->assertEquals(
            "Bourg-en-Bresse",
            $this->entiteListe->getInfoFromArray(array("1"))[0]['denomination']
        );
    }

    public function testGetAllFille()
    {
        $this->assertEquals(
            "CCAS",
            $this->entiteListe->getAllFille(1)[0]['denomination']
        );
    }

    public function testgetAllDescendant()
    {
        $this->assertEquals(
            "CCAS",
            $this->entiteListe->getAllDescendant(1)[0]['denomination']
        );
    }

    public function testGetDenomination()
    {
        $this->assertEquals(
            "Bourg-en-Bresse",
            $this->entiteListe->getDenomination("Bourg")[0]['denomination']
        );
    }

    public function testGetBySiren()
    {
        $this->assertEquals(
            "Bourg-en-Bresse",
            $this->entiteListe->getBySiren("123456789")[0]['denomination']
        );
    }
}
