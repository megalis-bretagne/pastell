<?php

use Pastell\Service\Connecteur\ConnecteurActionService;

class ConnecteurControlerTest extends ControlerTestCase
{
    /**
     * @var ConnecteurControler
     */
    private $connecteurControler;

    protected function setUp()
    {
        parent::setUp();
        $this->connecteurControler = $this->getControlerInstance(ConnecteurControler::class);
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function testEditionActionConnecteurDoesNotExists()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Ce connecteur n'existe pas");
        $this->connecteurControler->editionAction();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function testEditionAction()
    {
        $this->setGetInfo(['id_ce' => 11]);
        $this->expectOutputRegex("#Connecteur mailsec - mailsec : Mail securise#");
        $this->connecteurControler->editionAction();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function testEditionLibelleAction()
    {
        $this->setGetInfo(['id_ce' => 11]);
        $this->expectOutputRegex("#Connecteur mailsec - mailsec : Mail securise#");
        $this->connecteurControler->editionLibelleAction();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function testDoEditionLibelleFailed()
    {
        $this->expectException(LastErrorException::class);
        $this->expectExceptionMessage("Ce connecteur n'existe pas");
        $this->connecteurControler->doEditionLibelleAction();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function testEditionActionWhenConnecteurDefinitionDoesNotExists()
    {
        $connecteurEntiteSQL = $this->getObjectInstancier()->getInstance(ConnecteurEntiteSQL::class);
        $id_ce = $connecteurEntiteSQL->addConnecteur(
            1,
            "not_existing_connecteur",
            "signature",
            "foo"
        );
        $this->setGetInfo(['id_ce' => $id_ce]);
        $this->expectOutputRegex(
            "#Impossible d'afficher les propriétés du connecteur car celui-ci est inconnu sur cette plateforme Pastell#"
        );
        $this->connecteurControler->editionAction();
    }

    public function testWithAPI()
    {
        $connecteurEntiteSQL = $this->getObjectInstancier()->getInstance(ConnecteurEntiteSQL::class);
        $id_ce = $connecteurEntiteSQL->addConnecteur(
            1,
            "not_existing_connecteur",
            "signature",
            "foo"
        );
        $result = $this->getInternalAPI()->patch("/entite/1/connecteur/$id_ce/content/", ["foo" => "bar"]);
        $this->assertEquals('foo', $result['libelle']);
        $this->assertEquals('ok', $result['result']);
    }

    public function testDoExport()
    {
        $this->setPostInfo([
            'id_ce' => 11,
            'password' => '12345678',
            'password_check' => '12345678',
        ]);

        $this->expectOutputRegex("/Content-type: application\/json;*/");
        $this->connecteurControler->doExportAction();
    }

    /**
     * @throws LastErrorException
     * @throws Exception
     */
    public function testDoImport(): void
    {
        $_FILES = [
            'pser' => [
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => __DIR__ . '/fixtures/mailsec_export_12345678.json'
            ]
        ];
        $this->setPostInfo([
            'id_ce' => 11,
            'password' => '12345678',
        ]);

        try {
            $this->connecteurControler->doImportAction();
        } catch (LastMessageException $exception) {
            $this->assertSame(
                sprintf(
                    "Redirection vers %s/Connecteur/edition?id_ce=11: Les données du connecteur ont été importées",
                    rtrim(SITE_BASE, '/')
                ),
                $exception->getMessage()
            );
        }

        $this->assertSame(
            'test_secure_import@example.org',
            $this->getDonneesFormulaireFactory()
                ->getConnecteurEntiteFormulaire(11)
                ->get('mailsec_from')
        );

        $connecteurActionService = $this->getObjectInstancier()->getInstance(ConnecteurActionService::class);
        $connecteur_action_message = $connecteurActionService->getByIdCe("11")[0]['message'];
        $this->assertEquals("Les données du connecteur ont été importées", $connecteur_action_message);
    }

    /**
     * @throws LastMessageException
     */
    public function testExternalData(): void
    {
        $this->setGetInfo([
            'id_ce' => 11,
            'field' => 'unknown_field',
        ]);
        $this->expectException(LastErrorException::class);
        $this->expectExceptionMessageRegExp("/Le champ unknown_field n'existe pas/");

        $this->connecteurControler->externalDataAction();
    }
}
