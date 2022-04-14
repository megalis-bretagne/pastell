<?php

trait SoapUtilitiesTestTrait
{
    public function mockSoapClient(callable $__call_callback): void
    {
        $soapClient = $this->createMock(SoapClient::class);
        $soapClient
            ->method('__call')
            ->willReturnCallback($__call_callback);

        $soapClientFactory = $this->createMock(SoapClientFactory::class);
        $soapClientFactory
            ->method('getInstance')
            ->willReturn($soapClient);

        $this->getObjectInstancier()->setInstance(SoapClientFactory::class, $soapClientFactory);
    }

    public function returnSoapResponseFromXMLFile(string $filepath): stdClass
    {
        return json_decode(json_encode(simplexml_load_file($filepath)), false);
    }
}
