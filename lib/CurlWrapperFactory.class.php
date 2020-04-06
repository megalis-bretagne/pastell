<?php

class CurlWrapperFactory
{
    private $http_proxy_url;

    public function __construct(string $http_proxy_url = "")
    {
        $this->http_proxy_url = $http_proxy_url;
    }

    public function getInstance()
    {

        $curlWrapper = new CurlWrapper(new CurlFunctions());
        if ($this->http_proxy_url !== "") {
            $curlWrapper->setProxy($this->http_proxy_url);
        }
        return $curlWrapper;
    }
}
