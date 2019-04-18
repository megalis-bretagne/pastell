<?php

use Sabre\DAV\Client;
use Sabre\HTTP\ClientHttpException;
use Sabre\HTTP\Response;

class WebdavWrapperTest extends PHPUnit\Framework\TestCase {

    /**
     * @throws Exception
     */
    public function testExists() {
        $client = $this->getMockBuilder(Client::class)
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
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('propFind')
            ->willThrowException(new ClientHttpException(new Response()));

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
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('send')
            ->willReturn(new Response(200, ['Dav' => 'test']));

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

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('send')
            ->willReturn(new Response(200));


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

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('send')
            ->willReturn(new Response(403));


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
     * @throws Exception
     */
    public function testGet() {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('send')
            ->willReturn(new Response(200, [], 'content'));

        $webdavClientFactory = $this->getMockBuilder(WebdavClientFactory::class)->getMock();
        $webdavClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($client);

        /** @var WebdavClientFactory $webdavClientFactory */
        $webdavWrapper = new WebdavWrapper();
        $webdavWrapper->setWebdavClientFactory($webdavClientFactory);
        $webdavWrapper->setDataConnexion('https://domain.tld', '', '');

        $this->assertSame('content',$webdavWrapper->get('test.xml'));
    }

    /**
     * @throws Exception
     */
    public function testGetException() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("403 : Forbidden");

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('send')
            ->willReturn(new Response(403));

        $webdavClientFactory = $this->getMockBuilder(WebdavClientFactory::class)->getMock();
        $webdavClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($client);

        /** @var WebdavClientFactory $webdavClientFactory */
        $webdavWrapper = new WebdavWrapper();
        $webdavWrapper->setWebdavClientFactory($webdavClientFactory);
        $webdavWrapper->setDataConnexion('https://domain.tld', '', '');

        $webdavWrapper->get('test.xml');
    }
}