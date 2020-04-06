<?php

//Note : SoapClient crée un fichier de cache du WSDL, voir http://www.php.net/manual/en/soap.configuration.php

class SoapClientFactory
{
    private $http_proxy_url;

    public function __construct(string $http_proxy_url = "")
    {
        $this->http_proxy_url = $http_proxy_url;
    }

    /**
     * @param $wsdl
     * @param array $options
     * @param bool $is_jax_ws
     * @return NotBuggySoapClient
     * @throws SoapFault
     */
    public function getInstance($wsdl, array $options = array(), $is_jax_ws = false)
    {
        if ($this->http_proxy_url !== "") {
            // Needed to retrieve wsdl and w3c stuff or in non curl mode
            $url_part = parse_url($this->http_proxy_url);
            $options['proxy_host'] = $url_part['host'];
            $options['proxy_port'] = $url_part['port'];
        }

        $soapClient = new NotBuggySoapClient($wsdl, $options, $is_jax_ws);
        $soapClient->setProxy($this->http_proxy_url);
        return $soapClient;
    }
}
