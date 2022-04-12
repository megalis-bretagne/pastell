<?php

use Monolog\Logger;

class AsalaeRestTest extends PastellTestCase
{
    private function getAsalaeRest($curl_response, $http_code = 200, $chunk_size_in_bytes = 0)
    {
        $curlWrapper = $this->createMock('CurlWrapper');
        $curlWrapper->method('get')->willReturn(
            $curl_response
        );
        $curlWrapper->method('getHTTPCode')->willReturn($http_code);
        /** @var CurlWrapper $curlWrapper */


        $curlWrapperFactory = $this->createMock(CurlWrapperFactory::class);
        $curlWrapperFactory->method('getInstance')->willReturn($curlWrapper);
        /** @var  CurlWrapperFactory $curlWrapperFactory */

        $connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $connecteurConfig->setData("url", "https://qualif.taact-api.fr/restservices/	");
        $connecteurConfig->setData("chunk_size_in_bytes", $chunk_size_in_bytes);
        $asalaeRest =  new AsalaeREST($curlWrapperFactory, $this->getObjectInstancier()->getInstance(Logger::class));
        $asalaeRest->setConnecteurConfig($connecteurConfig);
        return $asalaeRest;
    }


    /**
     * @throws Exception
     */
    public function testPing()
    {
        $asalaeRest = $this->getAsalaeRest(
            '"webservices as@lae accessibles"'
        );
        $this->assertEquals("webservices as@lae accessibles", $asalaeRest->ping());
    }

    /**
     * @throws Exception
     */
    public function testPingNotResponding()
    {
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
    public function testPingFailed404()
    {
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
    public function testPingNoJson()
    {
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
    public function testGetVersion()
    {
        $asalaeRest = $this->getAsalaeRest(
            '{"application":"as@lae","denomination":"","version":"V1.6.3"}'
        );
        $this->assertEquals('V1.6.3', $asalaeRest->getVersion()['version']);
    }


    public function testGetErrorString()
    {
        $asalaeRest = $this->getAsalaeRest('');
        $this->assertEquals(
            'Erreur non identifié',
            $asalaeRest->getErrorString(42)
        );
    }

    /**
     * @throws Exception
     */
    public function testSendArchive()
    {
        $asalaeRest = $this->getAsalaeRest('"ok"');
        $this->assertTrue(
            $asalaeRest->sendArchive("test bordereau SEDA", __FILE__)
        );
    }

    /**
     * @throws Exception
     */
    public function testSendArchiveInChunk()
    {

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        copy(__DIR__ . "/../../fixtures/vide.pdf", $tmp_folder . "/archive.tar.gz");

        $chunksize = filesize(__DIR__ . "/../../fixtures/vide.pdf") / 2;

        $asalaeRest = $this->getAsalaeRest('{"chunk_session_identifier":12,"chunk_security_identifier":42}', 200, $chunksize);
        $this->assertTrue(
            $asalaeRest->sendArchive("test bordereau SEDA", $tmp_folder . "/archive.tar.gz")
        );
    }


    /**
     * @throws Exception
     */
    public function testSendArchiveInChunkFailed()
    {

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        copy(__DIR__ . "/../../fixtures/vide.pdf", $tmp_folder . "/archive.tar.gz");

        $chunksize = filesize(__DIR__ . "/../../fixtures/vide.pdf") / 2;

        $asalaeRest = $this->getAsalaeRest('"ok"', 200, $chunksize);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cette version d'as@lae ne permet pas l'envoi d'archive par morceaux");
        $asalaeRest->sendArchive("test bordereau SEDA", $tmp_folder . "/archive.tar.gz");
    }

    /**
     * @throws Exception
     */
    public function testSendArchiveFailed()
    {
        $asalaeRest = $this->getAsalaeRest('');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("");
        $asalaeRest->sendArchive("test bordereau SEDA", __FILE__);
    }

    public function testGetURL()
    {
        $asalaeRest = $this->getAsalaeRest('"ok"');
        $this->assertEquals(
            'https://qualif.taact-api.fr/archives/viewByArchiveIdentifier/42',
            $asalaeRest->getURL(42)
        );
    }
}
