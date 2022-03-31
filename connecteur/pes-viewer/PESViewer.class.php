<?php

class PESViewer extends Connecteur
{
    public const TEST_PES = "test_pes";
    public const CONNECTEUR_TYPE_ID = "visionneuse_pes";

    private DonneesFormulaire $connecteurConfig;

    public function __construct(
        private CurlWrapperFactory $curlWrapperFactory,
        private string $pes_viewer_url,
    ) {
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire): void
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    /**
     * @throws UnrecoverableException
     */
    public function getURL(string $pes_filepath): string
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

        if (preg_match('#https?://[^/]+(.*)#', $location, $matches)) {
            $location = $matches[1];
        }

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = [];
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        foreach ($cookies as $cookie => $value) {
            setcookie_wrapper($cookie, $value, time() + 3600, "/bl-xemwebviewer", httponly: true);
        }

        return $location;
    }

    private function getPrepareURL(): string
    {
        return trim($this->pes_viewer_url, "/") . "/bl-xemwebviewer/prepare";
    }

    /**
     * @throws UnrecoverableException
     */
    public function test(): string
    {
        return $this->getURL($this->connecteurConfig->getFilePath(self::TEST_PES));
    }
}
