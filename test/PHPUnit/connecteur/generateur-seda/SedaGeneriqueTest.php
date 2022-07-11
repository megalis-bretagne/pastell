<?php

declare(strict_types=1);

use Pastell\Seda\Message\SedaMessageBuilder;
use Pastell\Tests\Connector\AbstractSedaGeneratorConnectorTestCase;

class SedaGeneriqueTest extends AbstractSedaGeneratorConnectorTestCase
{
    public function getSedaMessageBuilder(): SedaMessageBuilder
    {
        return new SedaMessageBuilder($this->getTmpFolder());
    }

    public function getSedaConnectorId(): string
    {
        return 'generateur-seda';
    }

    public function getExpectedCallDirectory(): string
    {
        return __DIR__ . '/seda-test-cases';
    }
}
