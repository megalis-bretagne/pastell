<?php

class WebdavWrapperTest extends PHPUnit\Framework\TestCase {

    /**
     * @throws Exception
     */
    public function testExists() {
        $client = $this->getMockBuilder('\Sabre\DAV\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('propFind')
            ->willReturn(true);

        $webdavClientFactory = $this->getMockBuilder(WebdavClientFactory::class)->getMock();
        $webdavClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($client);

        /** @var WebdavClientFactory $webdavClientFactory */

        $webdavWrapper = new WebdavWrapper();
        $webdavWrapper->setWebdavClientFactory($webdavClientFactory);
        $webdavWrapper->setDataConnexion('i', 'j', 'k');
        $this->assertTrue($webdavWrapper->exists("test"));
    }

    /**
     * @throws Exception
     */
    public function testException() {
        $client = $this->getMockBuilder('\Sabre\DAV\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('propFind')
            ->willThrowException(new \Sabre\HTTP\ClientHttpException(new \Sabre\HTTP\Response()));

        $webdavClientFactory = $this->getMockBuilder(WebdavClientFactory::class)->getMock();
        $webdavClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($client);

        /** @var WebdavClientFactory $webdavClientFactory */

        $webdavWrapper = new WebdavWrapper();
        $webdavWrapper->setWebdavClientFactory($webdavClientFactory);
        $webdavWrapper->setDataConnexion('i', 'j', 'k');
        $this->expectException(Exception::class);
        $webdavWrapper->exists("test");
    }


    /**
     * @test
     * @throws Exception
     */
    public function whenWebdavConnectionIsOk() {
        $client = $this->getMockBuilder('\Sabre\DAV\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('send')
            ->willReturn(new \Sabre\HTTP\Response(200, ['Dav' => 'test']));

        $webdavClientFactory = $this->getMockBuilder(WebdavClientFactory::class)->getMock();
        $webdavClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($client);

        /** @var WebdavClientFactory $webdavClientFactory */

        $webdavWrapper = new WebdavWrapper();
        $webdavWrapper->setWebdavClientFactory($webdavClientFactory);
        $webdavWrapper->setDataConnexion('https://domain.tld', '', '');

        $this->assertTrue($webdavWrapper->isConnected());
    }


    /**
     * When we don't get a webdav header
     *
     * @test
     * @throws Exception
     */
    public function whenNoDavHeaderExist() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le serveur ne présente pas le header Dav");

        $client = $this->getMockBuilder('\Sabre\DAV\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('send')
            ->willReturn(new \Sabre\HTTP\Response(200));


        $webdavClientFactory = $this->getMockBuilder(WebdavClientFactory::class)->getMock();
        $webdavClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($client);

        /** @var WebdavClientFactory $webdavClientFactory */

        $webdavWrapper = new WebdavWrapper();
        $webdavWrapper->setWebdavClientFactory($webdavClientFactory);
        $webdavWrapper->setDataConnexion('https://domain.tld', '', '');

        $webdavWrapper->isConnected();
    }

    /**
     * When we are not authorized to authenticate to the webdav server
     *
     * @test
     * @throws Exception
     */
    public function whenDavAccessIsForbidden() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("403 : Forbidden");

        $client = $this->getMockBuilder('\Sabre\DAV\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('send')
            ->willReturn(new \Sabre\HTTP\Response(403));


        $webdavClientFactory = $this->getMockBuilder(WebdavClientFactory::class)->getMock();
        $webdavClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($client);

        /** @var WebdavClientFactory $webdavClientFactory */

        $webdavWrapper = new WebdavWrapper();
        $webdavWrapper->setWebdavClientFactory($webdavClientFactory);
        $webdavWrapper->setDataConnexion('https://domain.tld', '', '');

        $webdavWrapper->isConnected();
    }
}