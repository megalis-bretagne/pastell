<?php

class ProxyNeeded
{
    private $http_proxy_url;
    private $no_proxy;

    public function __construct(string $http_proxy_url = "", string $no_proxy = "")
    {
        $this->http_proxy_url = $http_proxy_url;
        $this->no_proxy = $no_proxy;
    }

    public function isNeeded(string $url): bool
    {
        if (! $this->http_proxy_url) {
            return false;
        }
        if (! $this->no_proxy) {
            return true;
        }
        $no_proxy_array = explode(",", $this->no_proxy);
        $host = parse_url($url, PHP_URL_HOST);
        return (! in_array($host, $no_proxy_array));
    }
}
