<?php

declare(strict_types=1);

use Pastell\Seda\Message\SedaMessageBuilder;
use Pastell\Tests\Connector\AbstractSedaGeneratorConnectorTestCase;

final class SedaGeneratorAsalae10Test extends AbstractSedaGeneratorConnectorTestCase
{
    public function getSedaMessageBuilder(): SedaMessageBuilder
    {
        return new SedaMessageBuilder($this->getTmpFolder());
    }

    public function getSedaConnectorId(): string
    {
        return 'generateur-seda-asalae-1.0';
    }

    public function getExpectedCallDirectory(): string
    {
        return __DIR__ . '/seda-test-cases';
    }
}
