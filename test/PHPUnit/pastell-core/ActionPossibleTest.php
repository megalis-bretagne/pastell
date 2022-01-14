<?php

class ActionPossibleTest extends PastellTestCase
{
    /** @var  ActionPossible */
    private $actionPossible;

    private $id_d;

    protected function setUp()
    {
        parent::setUp();
        $this->actionPossible = $this->getObjectInstancier()->getInstance(ActionPossible::class);
        $info = $this->getInternalAPI()->post("entite/1/document/", array("type" => "test"));
        $this->id_d = $info['id_d'];
    }

    /**
     * @throws Exception
     */
    public function testIsCreationPossible()
    {
        $this->assertTrue($this->actionPossible->isCreationPossible(1, 1, 'test'));
    }


    /**
     * @throws Exception
     */
    public function testIsModificationPossible()
    {
        $id_d = $this->createDocument('actes-generique')['id_d'];
        $this->assertTrue($this->actionPossible->isActionPossible(1, 1, $id_d, "modification"));
    }

    /**
     * @throws Exception
     */
    public function testGetActionPossible()
    {
        $result = $this->actionPossible->getActionPossible(1, 1, $this->id_d);
        $this->assertEquals('modification', $result[0]);
    }


    /**
     * @throws Exception
     */
    public function testGetLastBadRule()
    {
        $info = $this->getInternalAPI()->post("entite/1/document/", array("type" => "actes-generique"));
        $id_d = $info['id_d'];
        $this->assertFalse($this->actionPossible->isActionPossible(1, 1, $id_d, 'send-tdt'));
        $this->assertEquals("document_is_valide n'est pas vérifiée", $this->actionPossible->getLastBadRule());
    }


    /**
     * @throws Exception
     */
    public function testFatalErrorIsAlwaysPossible()
    {
        $this->assertTrue($this->actionPossible->isActionPossible(1, 1, $this->id_d, 'fatal-error'));
    }

    /**
     * @throws Exception
     */
    public function testUnableToCreateForRootEntity()
    {
        $this->assertFalse($this->actionPossible->isCreationPossible(0, 1, 'test'));
    }

    /**
     * @throws Exception
     */
    public function testUnableToCreateForInactiveEntity()
    {
        $entiteSQL = $this->getObjectInstancier()->getInstance(EntiteSQL::class);
        $entiteSQL->setActive(1, false);
        $this->assertFalse($this->actionPossible->isCreationPossible(1, 1, 'test'));
    }

    /**
     * @throws Exception
     */
    public function testveriTypeEntite()
    {
        $this->assertTrue($this->actionPossible->isCreationPossible(1, 1, 'actes-generique'));
    }

    /**
     * @throws Exception
     */
    public function testUserRootEnableAction()
    {
        $this->assertTrue($this->actionPossible->isActionPossible(1, 0, $this->id_d, 'fatal-error'));
    }

    /**
     * @throws Exception
     */
    public function testVerifOk()
    {
        $id_d = $this->createDocument("mailsec")['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData([
            "to" => "foo@bar.fz",
            "objet" => "baz",
            "message" => "buz",
        ]);

        $this->assertEquals(
            ['modification','supression','envoi'],
            $this->actionPossible->getActionPossible(1, 1, $id_d)
        );
    }

    /**
     * @throws Exception
     */
    public function testVerifContentOK()
    {
        $id_d = $this->createDocument("actes-generique")['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData([
            "acte_nature" => "1",
            "numero_de_lacte" => "201901141524",
            "objet" => "baz",
            'date_de_lacte' => "2019-01-14",
            "classification" => "1.1",
            "envoi_tdt" => true,
            'type_piece' => 'ok',
        ]);

        $donneesFormulaire->addFileFromData("arrete", "foo.pdf", "bar");
        $donneesFormulaire->addFileFromData("type_piece_fichier", "foo.json", "bar");

        $this->assertEquals(
            ['modification','supression','send-tdt'],
            $this->actionPossible->getActionPossible(1, 1, $id_d)
        );
    }

    /**
     * @throws Exception
     */
    public function testVerifContentKO()
    {
        $id_d = $this->createDocument("actes-generique")['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData([
            "acte_nature" => "1",
            "numero_de_lacte" => "201901141524",
            "objet" => "baz",
            'date_de_lacte' => "2019-01-14",
            "classification" => "1.1",
            'type_piece' => 'ok',
        ]);

        $donneesFormulaire->addFileFromData("arrete", "foo.pdf", "bar");
        $donneesFormulaire->addFileFromData("type_piece_fichier", "foo.json", "bar");

        $this->assertEquals(
            ['modification','supression'],
            $this->actionPossible->getActionPossible(1, 1, $id_d)
        );
    }

    /**
     * @throws Exception
     */
    public function testIsActionPossibleOnConnecteur()
    {
        $id_ce = $this->createConnector('s2low', "Connecteur s2low", 1)['id_ce'];
        $this->assertEquals(
            [
                'test-tedetis',
                'test-rgs-connexion',
                'demande-classification',
                'recup-classification',
                'recup-pes-retour',
                'recup-reponse-prefecture',
            ],
            $this->actionPossible->getActionPossibleOnConnecteur($id_ce, 1)
        );
    }

    /**
     * @throws Exception
     */
    public function testPasDansUnLot()
    {
        $id_d = $this->createDocument("actes-generique")['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData([
            "acte_nature" => "1",
            "numero_de_lacte" => "201901141524",
            "objet" => "baz",
            'date_de_lacte' => "2019-01-14",
            "classification" => "1.1",
            "envoi_tdt" => true,
            'type_piece' => 'ok',
        ]);

        $donneesFormulaire->addFileFromData("arrete", "foo.pdf", "bar");
        $donneesFormulaire->addFileFromData("type_piece_fichier", "foo.json", "bar");

        $this->assertEquals(
            ['supression','send-tdt'],
            $this->actionPossible->getActionPossibleLot(1, 1, $id_d)
        );
    }
}
