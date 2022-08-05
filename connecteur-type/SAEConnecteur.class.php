<?php

declare(strict_types=1);

abstract class SAEConnecteur extends Connecteur
{
    abstract public function sendArchive(string $bordereau, string $archivePath): string;

    abstract public function provideAcknowledgment(): bool;

    /**
     * @throws UnrecoverableException
     */
    abstract public function getAck(string $transfertId, string $originatingAgencyId): string;

    /**
     * @throws UnrecoverableException
     */
    abstract public function getAtr(string $transfertId, string $originatingAgencyId): string;
}
