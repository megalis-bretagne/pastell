<?php

declare(strict_types=1);

use IparapheurV5Client\Exception\IparapheurV5Exception;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class RecupFinParapheurTestConnexion extends ActionExecutor
{
    /**
     * @throws \Http\Client\Exception
     * @throws ExceptionInterface
     * @throws IparapheurV5Exception
     * @throws Exception
     */
    public function go(): bool
    {
        /** @var RecupFinParapheur $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        $message = $recupParapheur->testConnexion();
        $this->setLastMessage($message);
        return true;
    }
}
