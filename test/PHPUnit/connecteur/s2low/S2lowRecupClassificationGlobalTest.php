<?php

class S2lowRecupClassificationGlobalTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    private function getS2low($curl_response, $id_e): array
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


        return $this->createConnector('s2low', "S2LOW", $id_e);
    }


    /**
     * When getting latest available classification file
     *
     * @test
     */
    public function whenGettingLatestClassification()
    {
        $connectorFirstInstance = $this->getS2low(file_get_contents(__DIR__ . '/fixtures/999-1234----7-2_1.xml'), self::ID_E_COL);
        $connectorSecondInstance = $this->getS2low(file_get_contents(__DIR__ . '/fixtures/999-1234----7-2_1.xml'), self::ID_E_COL);
        $connectorLastInstance = $this->getS2low(file_get_contents(__DIR__ . '/fixtures/999-1234----7-2_7.xml'), self::ID_E_SERVICE);


        $globalConnector = $this->createConnector('s2low', 'S2low', 0);
        $actionResult = $this->triggerActionOnConnector($globalConnector['id_ce'], 'recup-classification');

        $this->assertTrue($actionResult);

        $expectedMessage = "Résultat :"
            . "<br/>Bourg-en-Bresse(id_ce=14) : classification récupérée"
            . "<br/>Bourg-en-Bresse(id_ce=15) : classification récupérée"
            . "<br/>CCAS(id_ce=16) : classification récupérée";

        $this->assertLastMessage($expectedMessage);

        $connectorConfig = $this->getConnecteurFactory()->getConnecteurConfig($connectorLastInstance['id_ce']);

        $this->assertSame('2018-11-28', $connectorConfig->get('classification_date'));
        $this->assertSame(
            file_get_contents(__DIR__ . '/fixtures/999-1234----7-2_7.xml'),
            $connectorConfig->getFileContent('classification_file')
        );
    }
}
