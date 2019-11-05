<?php


trait CurlUtilitiesTestTrait
{


    protected function mockCurl(array $url_to_content)
    {
        $this->mockCurlWithCallable(
            function ($url) use ($url_to_content) {
                if (! isset($url_to_content[$url])) {
                    throw new UnrecoverableException("Appel à une URL inatendue $url");
                }
                return $url_to_content[$url];
            }
        );
    }

    protected function mockCurlWithCallable(callable $get_function)
    {
        $curlWrapper = $this->getMockBuilder(CurlWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $curlWrapper->expects($this->any())
            ->method('get')
            ->willReturnCallback($get_function);

        $curlWrapper->expects($this->any())
            ->method('getHTTPCode')
            ->willReturn(200);

        $curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $curlWrapperFactory->expects($this->any())
            ->method('getInstance')
            ->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);
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
