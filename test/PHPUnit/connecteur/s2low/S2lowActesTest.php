<?php

class S2lowActesTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testPostActesOK()
    {
        $info = $this->createConnector('s2low', "S2LOW", self::ID_E_COL);
        $id_ce = $info['id_ce'];
        $this->associateFluxWithConnector($id_ce, "actes-generique", "TdT", self::ID_E_COL);

        $connecteurDonnerFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);

        $connecteurDonnerFormulaire->addFileFromCopy(
            "classification_file",
            "classification.xml",
            __DIR__ . "/fixtures/classification-exemple.xml"
        );


        $info = $this->createDocument('actes-generique', self::ID_E_COL);

        $id_d = $info['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->addFileFromData("arrete", "test.pdf", "aaaa");
        $donneesFormulaire->addFileFromData("autre_document_attache", "annexe1.pdf", "foo");

        $donneesFormulaire->setTabData(
            [
                'acte_nature' => '3',
                'numero_de_lacte' => '201903251130',
                'objet' => 'TEST',
                'date_de_lacte' => '2019-03-25',
                'classification' => '2.1'
            ]
        );

        $curlWrapper = $this->getMockBuilder('CurlWrapper')
            ->disableOriginalConstructor()
            ->getMock();

        $curlWrapper->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($url) {
                if ($url == "/modules/actes/actes_classification_fetch.php?api=1") {
                    return file_get_contents(__DIR__ . "/fixtures/classification-exemple.xml");
                } elseif ($url == "/admin/users/api-list-login.php") {
                    return true;
                } elseif ($url == "/modules/actes/actes_transac_create.php") {
                    return "OK\n666";
                }
                throw new Exception("$url inatendu");
            }));

        $addPostDataCall = [];

        $curlWrapper->expects($this->any())
            ->method('addPostData')
            ->will($this->returnCallback((function ($key, $value) use (&$addPostDataCall) {
                $addPostDataCall[$key] = $value;
                return true;
            })));

        $curlWrapperFactory = $this->getMockBuilder('CurlWrapperFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $curlWrapperFactory->expects($this->any())
            ->method('getInstance')
            ->willReturn($curlWrapper);


        $this->getObjectInstancier()->setInstance('CurlWrapperFactory', $curlWrapperFactory);

        /** @var S2low $s2low */
        $s2low = $this->getConnecteurFactory()->getConnecteurById($id_ce);

        $this->assertTrue($s2low->postActes($donneesFormulaire));

        $this->assertEquals("666", $donneesFormulaire->get('tedetis_transaction_id'));

        $this->assertEquals(
            [
                'api' => 1,
                'nature_code' => '3',
                'number' => '201903251130',
                'subject' => 'TEST',
                'decision_date' => '2019-03-25',
                'en_attente' => 0,
                'document_papier' => 0,
                'type_acte' => '99_AI',
                'type_pj[]' => '99_AI',
                'classif1' => '2',
                'classif2' => '1',
             ],
            $addPostDataCall
        );
    }
}
