<?php

declare(strict_types=1);

use Pastell\Connector\AbstractSedaGeneratorConnector;
use Pastell\Seda\SedaVersion;

final class SedaGenerique extends AbstractSedaGeneratorConnector
{
    public const CONNECTEUR_TYPE_ID = 'generateur-seda';
    public const CONNECTEUR_GLOBAL_TYPE = 'Generateur SEDA';

    public function getVersion(): SedaVersion
    {
        throw new BadMethodCallException('Not implemented');
    }
}
