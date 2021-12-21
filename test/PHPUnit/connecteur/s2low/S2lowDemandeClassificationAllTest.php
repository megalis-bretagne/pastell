<?php

class S2lowDemandeClassificationAllTest extends PastellTestCase
{
    /**
     * @param $curl_response
     * @param $id_e
     * @return array
     */
    private function getS2low($curl_response, $id_e)
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


        $info = $this->createConnector('s2low', "S2LOW", $id_e);

        return $info;
    }


    /**
     * When getting latest available classification file
     *
     * @test
     * @throws Exception
     */
    public function whenGettingLatestClassification()
    {
        $this->getS2low('S²low a répondu : OK', self::ID_E_COL);
        $this->getS2low('S²low a répondu : OK', self::ID_E_SERVICE);


        $globalConnector = $this->createConnector('s2low', 'S2low', 0);
        $actionResult = $this->triggerActionOnConnector($globalConnector['id_ce'], 'demande-classification');

        $this->assertTrue($actionResult);

        $expectedMessage = "Résultat :"
            . "<br/>Bourg-en-Bresse(id_ce=14) : demande de classification envoyée"
            . "<br/>CCAS(id_ce=15) : demande de classification envoyée";

        $this->assertLastMessage($expectedMessage);
    }
}
