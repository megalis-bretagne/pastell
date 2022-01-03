<?php

trait SoapUtilitiesTestTrait
{
    /**
     * @param callable $__call_callback
     */
    public function mockSoapClient(callable $__call_callback): void
    {

        $soapClient = $this->createMock(SoapClient::class);
        $soapClient
            ->expects($this->any())
            ->method('__call')
            ->will($this->returnCallback($__call_callback));

        $soapClientFactory = $this->createMock(SoapClientFactory::class);
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        $this->getObjectInstancier()->setInstance(SoapClientFactory::class, $soapClientFactory);
    }

    public function returnSoapResponseFromXMLFile(string $filepath): stdClass
    {
        return json_decode(json_encode(simplexml_load_file($filepath)), false);
    }

    /**
     * @param $classname
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    abstract public function getMockBuilder($classname);


    /**
     * @return ObjectInstancier
     */
    abstract public function getObjectInstancier();


    abstract public function any();
    abstract public function returnCallback(callable $function);
}
