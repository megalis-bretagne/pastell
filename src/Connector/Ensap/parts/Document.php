<?php

declare(strict_types=1);

namespace Pastell\Connector\Ensap\parts;

class Document
{
    public int $theme;
    public int $sstheme;
    public string $dateDocument;
    public ?string $montant;
    public string $nomFichier;
}
