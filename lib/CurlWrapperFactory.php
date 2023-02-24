<?php

class CurlWrapperFactory
{
    private $http_proxy_url;
    private $no_proxy;

    public function __construct(string $http_proxy_url = "", string $no_proxy = "")
    {
        $this->http_proxy_url = $http_proxy_url;
        $this->no_proxy = $no_proxy;
    }

    public function getInstance()
    {
        $curlWrapper = new CurlWrapper(new CurlFunctions());
        if ($this->http_proxy_url !== "") {
                $curlWrapper->setProxy($this->http_proxy_url);
                $curlWrapper->setNoProxy($this->no_proxy);
        }
        return $curlWrapper;
    }
}
