<?php

declare(strict_types=1);

namespace Pastell\Connector\Ensap\enveloppe;

class Gestionnaire
{
    public ?string $codeGestion;
    public ?string $codePoste;
    public string $siret;
    public array $documents;
}