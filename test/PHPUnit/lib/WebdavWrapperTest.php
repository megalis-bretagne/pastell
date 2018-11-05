<?php

class WebdavWrapperTest extends PHPUnit\Framework\TestCase  {

	/**
	 * @throws Exception
	 */
	public function testExists(){
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
		$webdavWrapper->setDataConnexion('i','j','k');
		$this->assertTrue($webdavWrapper->exists("test"));
	}

	/**
	 * @throws Exception
	 */
	public function testException(){
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
		$webdavWrapper->setDataConnexion('i','j','k');
		$this->expectException(Exception::class);
		$webdavWrapper->exists("test");
	}





}