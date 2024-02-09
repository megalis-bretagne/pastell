<?php

declare(strict_types=1);

namespace Pastell\Connector\Ensap\enveloppe;

class Assure
{
    public string $numeroDossier;
    public string $numeroOrdre;
    public string $nir;
    public ?string $nirModifie;
    public string $nomNaissance;
    public ?string $sexe;
    public string $dateNaissance;
    public string $iban;
    public string $statut;
    public ?string $referenceEmetteur;
    public array $gestionnaires;
}
