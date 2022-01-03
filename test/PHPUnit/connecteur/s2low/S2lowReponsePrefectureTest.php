<?php

require_once  __DIR__ . '/../../../../connecteur/s2low/S2low.class.php';

class S2lowReponsePrefectureTest extends PastellTestCase
{
    /**
     * @param $curl_response
     * @return Connecteur
     * @throws Exception
     */
    private function getS2low($curl_response)
    {
        $curlWrapper = $this->createMock(CurlWrapper::class);

        $curlWrapper
            ->method('get')
            ->willReturn($curl_response);

        $curlWrapperFactory = $this->createMock(CurlWrapperFactory::class);

        $curlWrapperFactory
            ->method('getInstance')
            ->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);


        $info = $this->createConnector('s2low', "S2LOW", "1");

        $collectiviteProperties = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($info['id_ce']);
        $collectiviteProperties->addFileFromCopy("classification_file", "classification.xml", __DIR__ . "/fixtures/classification-exemple.xml");

        return $this->getConnecteurFactory()->getConnecteurById($info['id_ce']);
    }

    /**
     * @throws S2lowException
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testSendReponseType()
    {
        /** @var S2low $s2low */
        $s2low = $this->getS2low("OK\n52");

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $donneesFormulaire->setTabData([
            'type_reponse' => TdtConnecteur::DEMANDE_PIECE_COMPLEMENTAIRE,
            'type_acte_demande_piece_complementaire' => '99_AI',
            'acte_nature' => 3,
            'type_pj_demande_piece_complementaire' => json_encode(['99_AU','AA_11']),
        ]);

        $donneesFormulaire->addFileFromData("reponse", "foo.pdf", "bar");
        $donneesFormulaire->addFileFromData("reponse_pj_demande_piece_complementaire", "baz.pdf", "buz", 0);
        $donneesFormulaire->addFileFromData("reponse_pj_demande_piece_complementaire", "baz2.pdf", "buz2", 1);

        $s2low->sendResponse($donneesFormulaire);

        $this->assertEquals(52, $donneesFormulaire->get('response_transaction_id'));
    }
}
