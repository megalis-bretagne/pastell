<?php

use Pastell\Client\Crypto\CryptoClientException;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * @deprecated 4.0.0
 */
final class LibersignConnectionTest extends ActionExecutor
{
    /**
     * @throws CryptoClientException
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function go()
    {
        /** @var Libersign $connector */
        $connector = $this->getMyConnecteur();
        $result = $connector->testConnection();
        $this->setLastMessage("La connexion est réussie : " . $result);
        return true;
    }
}
