<?php

declare(strict_types=1);

namespace Pastell\Connector\Ensap\enveloppe;

class Message
{
    public string $versionFichier;
    public string $natureFlux;
    public string $nomFichier;
    public string $dateTraitement;
}