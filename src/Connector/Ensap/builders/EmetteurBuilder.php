<?php

namespace Pastell\Connector\Ensap\builders;

use Pastell\Connector\Ensap\parts\Emetteur;

class EmetteurBuilder
{
    private Emetteur $emetteur;

    public function __construct()
    {
        $this->emetteur = new Emetteur();
    }

    public function setCodeEmetteur(string $codeEmetteur): self
    {
        $this->emetteur->codeEmetteur = $codeEmetteur;
        return $this;
    }

    public function setCodeCFT(string $codeCFT): self
    {
        $this->emetteur->codeCFT = $codeCFT;
        return $this;
    }

    public function build(): Emetteur
    {
        return $this->emetteur;
    }
}