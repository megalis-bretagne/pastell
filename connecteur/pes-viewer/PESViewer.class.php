<?php

class PESViewer extends Connecteur
{

    public const URL = "url";
    public const TEST_PES = "test_pes";
    public const CONNECTEUR_TYPE_ID = "pes-viewer";


    /** @var DonneesFormulaire */
    private $connecteurConfig;

    private $curlWrapperFactory;

    public function __construct(CurlWrapperFactory $curlWrapperFactory)
    {
        $this->curlWrapperFactory = $curlWrapperFactory;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    /**
     * @param $pes_filepath
     * @return mixed
     * @throws UnrecoverableException
     */
    public function getURL($pes_filepath)
    {

        $curlWrapper = $this->curlWrapperFactory->getInstance();
        $curlWrapper->addPostFile('file', $pes_filepath);
        $curlWrapper->setProperties(CURLOPT_FOLLOWLOCATION, false);
        $curlWrapper->setProperties(CURLOPT_HEADER, 1);
        $curlWrapper->dontVerifySSLCACert();
        $result = $curlWrapper->get($this->getPrepareURL());

        if ($curlWrapper->getLastHttpCode() != 302) {
            throw new UnrecoverableException("Error : " . $curlWrapper->getLastError());
        }

        preg_match('/^Location:\s*([^\n]*)/mi', $result, $matches);

        $location = $matches[1];

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = array();
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        foreach ($cookies as $cookie => $value) {
            setcookie_wrapper($cookie, $value, time() + 3600, "/bl-xemwebviewer; HttpOnly");
        }

        return $location;
    }

    private function getPrepareURL()
    {
        return trim($this->connecteurConfig->get(self::URL), "/") . "/bl-xemwebviewer/prepare";
    }

    /**
     * @return mixed
     * @throws UnrecoverableException
     */
    public function test()
    {
        return $this->getURL($this->connecteurConfig->getFilePath(self::TEST_PES));
    }
}