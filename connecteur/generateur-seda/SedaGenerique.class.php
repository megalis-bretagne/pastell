<?php

declare(strict_types=1);

use Pastell\Connector\AbstractSedaGeneratorConnector;

final class SedaGenerique extends AbstractSedaGeneratorConnector
{
    public const CONNECTEUR_TYPE_ID = 'generateur-seda';
    public const CONNECTEUR_GLOBAL_TYPE = 'Generateur SEDA';
}
