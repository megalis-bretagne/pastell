<?php

namespace Pastell\System\Check;

use Pastell\Service\Crypto;
use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;

class ExpectedElementsCheck implements CheckInterface
{
    /**
     * @var \VerifEnvironnement
     */
    private $verifEnvironnement;
    /**
     * @var \SQLQuery
     */
    private $sqlQuery;

    public function __construct(\VerifEnvironnement $verifEnvironnement, \SQLQuery $sqlQuery)
    {
        $this->verifEnvironnement = $verifEnvironnement;
        $this->sqlQuery = $sqlQuery;
    }

    public function check(): array
    {
        if (function_exists('curl_version')) {
            $curlVersion = curl_version()['ssl_version'];
        } else {
            $curlVersion = "La fonction curl_version() n'existe pas !";
        }

        $array = [
            'PHP est en version 8.1' => [
                '#^8\.1#',
                $this->verifEnvironnement->checkPHP()['environnement_value']
            ],
            'OpenSSL est en version 1 ou 3' => [
                "#^OpenSSL [13]\.#",
                shell_exec(OPENSSL_PATH . ' version')
            ],
            'Curl est compilé avec OpenSSL' => [
                '#OpenSSL#',
                $curlVersion
            ],
            'La base de données est accédée en UTF-8' => [
                "#^utf8$#",
                $this->sqlQuery->getClientEncoding()
            ]
        ];

        $elements = [];
        foreach ($array as $key => $value) {
            $elements[] = (new HealthCheckItem($key, $value[1], $value[0]))
                ->setSuccess((bool)preg_match($value[0], $value[1]));
        }

        $elements[] = (new HealthCheckItem(
            'Libsodium est en version >=' . Crypto::LIBSODIUM_MINIMUM_VERSION_EXPECTED,
            SODIUM_LIBRARY_VERSION,
            ">= " . Crypto::LIBSODIUM_MINIMUM_VERSION_EXPECTED
        ))->setSuccess(version_compare(
            SODIUM_LIBRARY_VERSION,
            Crypto::LIBSODIUM_MINIMUM_VERSION_EXPECTED,
            '>='
        ));

        return $elements;
    }
}
