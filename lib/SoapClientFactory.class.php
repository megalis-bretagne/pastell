<?php

//Note : SoapClient crée un fichier de cache du WSDL, voir http://www.php.net/manual/en/soap.configuration.php

class SoapClientFactory {

	public function getInstance($wsdl,array $options = array(),$is_jax_ws = false){
		return new NotBuggySoapClient($wsdl, $options,$is_jax_ws);
    }
}
