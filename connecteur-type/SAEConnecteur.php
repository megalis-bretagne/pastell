<?php

declare(strict_types=1);

use Pastell\Helpers\SedaHelper;

abstract class SAEConnecteur extends Connecteur
{
    /**
     * @return string The id of the SIP on the SAE
     */
    abstract public function sendSIP(string $bordereau, string $archivePath): string;

    abstract public function provideAcknowledgment(): bool;

    /**
     * @throws UnrecoverableException
     */
    abstract public function getAck(string $transfertId, string $originatingAgencyId): string;

    /**
     * @throws UnrecoverableException
     */
    abstract public function getAtr(string $transfertId, string $originatingAgencyId): string;

    abstract public function getSedaHelper(): SedaHelper;
}
