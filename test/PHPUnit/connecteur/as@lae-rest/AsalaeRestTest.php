<?php

require_once __DIR__."/../../../../connecteur/as@lae-rest/AsalaeREST.class.php";

class AsalaeRestTest extends PastellTestCase {


	private function getAsalaeRest($curl_response,$http_code = 200){
		$curlWrapper = $this->getMockBuilder('CurlWrapper')->getMock();
		$curlWrapper->expects($this->any())->method('get')->willReturn(
			$curl_response
		);
		$curlWrapper->expects($this->any())->method('getHTTPCode')->willReturn($http_code);
		/** @var CurlWrapper $curlWrapper */


		$curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)->getMock();
		$curlWrapperFactory->expects($this->any())->method('getInstance')->willReturn($curlWrapper);
		/** @var  CurlWrapperFactory $curlWrapperFactory */

		$connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
		$connecteurConfig->setData("url","https://qualif.taact-api.fr/restservices/	");
		$asalaeRest =  new AsalaeREST($curlWrapperFactory);
		$asalaeRest->setConnecteurConfig($connecteurConfig);
		return $asalaeRest;
	}


	/**
	 * @throws Exception
	 */
	public function testPing(){
		$asalaeRest = $this->getAsalaeRest(
			'"webservices as@lae accessibles"'
		);
		$this->assertEquals("webservices as@lae accessibles",$asalaeRest->ping());
	}

	/**
	 * @throws Exception
	 */
	public function testPingNotResponding(){
		$asalaeRest = $this->getAsalaeRest(
			''
		);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage("");
		$asalaeRest->ping();
	}

	/**
	 * @throws Exception
	 */
	public function testPingFailed404(){
		$asalaeRest = $this->getAsalaeRest(
			'"test"',
			404
		);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('"test" - code d\'erreur HTTP : 404');
		$asalaeRest->ping();
	}

	/**
	 * @throws Exception
	 */
	public function testPingNoJson(){
		$asalaeRest = $this->getAsalaeRest(
			'toto'
		);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Le serveur As@lae n\'a pas renvoyé une réponse compréhensible - problème de configuration ? : toto');
		$asalaeRest->ping();
	}

	/**
	 * @throws Exception
	 */
	public function testGetVersion(){
		$asalaeRest = $this->getAsalaeRest(
			'{"application":"as@lae","denomination":"","version":"V1.6.3"}'
		);
		$this->assertEquals('V1.6.3',$asalaeRest->getVersion()['version']);
	}


	public function testGetErrorString(){
		$asalaeRest = $this->getAsalaeRest('');
		$this->assertEquals('Erreur non identifié',
			$asalaeRest->getErrorString(42)
		);
	}

	/**
	 * @throws Exception
	 */
	public function testSendArchive(){
		$asalaeRest = $this->getAsalaeRest('"ok"');
		$this->assertTrue(
			$asalaeRest->sendArchive("test bordereau SEDA",__FILE__)
		);
	}

	/**
	 * @throws Exception
	 */
	public function testSendArchiveFailed(){
		$asalaeRest = $this->getAsalaeRest('');
		$this->expectException(Exception::class);
		$this->expectExceptionMessage("");
		$asalaeRest->sendArchive("test bordereau SEDA",__FILE__);
	}

	/**
	 * @throws Exception
	 */
	public function testGetAcuseReception(){
		$asalaeRest = $this->getAsalaeRest('"ok"');
		$this->assertEquals('"ok"',
			$asalaeRest->getAcuseReception(42)
		);
	}


	/**
	 * @throws Exception
	 */
	public function testGetReply(){
		$asalaeRest = $this->getAsalaeRest('"ok"');
		$this->assertEquals('"ok"',
			$asalaeRest->getReply(42)
		);
	}

	/**
	 * @throws Exception
	 */
	public function testGetLastErrorCode(){
		$asalaeRest = $this->getAsalaeRest('"ok"');
		$this->assertNull($asalaeRest->getLastErrorCode());
	}

	public function testGetURL(){
		$asalaeRest = $this->getAsalaeRest('"ok"');
		$this->assertEquals(
			'https://qualif.taact-api.fr/archives/viewByArchiveIdentifier/42',
			$asalaeRest->getURL(42)
		);
	}

}