<?php

//Note : SoapClient crée un fichier de cache du WSDL, voir http://www.php.net/manual/en/soap.configuration.php

class SoapClientFactory
{
    private $http_proxy_url;
    private $no_proxy;
    private $proxyNeeded;

    public function __construct(string $http_proxy_url = "", string $no_proxy = "")
    {
        $this->http_proxy_url = $http_proxy_url;
        $this->no_proxy = $no_proxy;
        $this->proxyNeeded = new ProxyNeeded($http_proxy_url, $no_proxy);
    }

    /**
     * @param $wsdl
     * @param array $options
     * @param bool $is_jax_ws
     * @return NotBuggySoapClient
     * @throws SoapFault
     */
    public function getInstance($wsdl, array $options = array(), bool $is_jax_ws = false)
    {
        if ($this->http_proxy_url !== "" && $this->proxyNeeded->isNeeded($wsdl)) {
            // Needed to retrieve wsdl and w3c stuff or in non curl mode
            $url_part = parse_url($this->http_proxy_url);
            $options['proxy_host'] = $url_part['host'];
            $options['proxy_port'] = $url_part['port'];
            $options['proxy_login'] = $url_part['user'] ?? '';
            $options['proxy_password'] = $url_part['pass'] ?? '';

            stream_context_set_option(
                $options['stream_context'],
                [
                    "http" => [
                       'proxy' => $this->http_proxy_url,
                        'request_fulluri' => true
                    ]
                ]
            );
        }

        $soapClient = new NotBuggySoapClient($wsdl, $options, $is_jax_ws);
        $soapClient->setProxy($this->http_proxy_url);
        $soapClient->setNoProxy($this->no_proxy);
        return $soapClient;
    }
}
