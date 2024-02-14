<?php

namespace Pastell\Connector\Ensap\builders;

use Pastell\Connector\Ensap\parts\Document;
use Pastell\Connector\Ensap\parts\Gestionnaire;
use PhpParser\Comment\Doc;

class GestionnaireBuilder
{
    private Gestionnaire $gestionnaire;

    public function __construct()
    {
        $this->gestionnaire = new Gestionnaire();
    }

    public function setCodeGestion(string $codeGestion): self
    {
        $this->gestionnaire->codeGestion = $codeGestion;
        return $this;
    }

    public function setCodePoste(string $codePoste): self
    {
        $this->gestionnaire->codePoste = $codePoste;
        return $this;
    }

    public function setSiret(string $siret): self
    {
        $this->gestionnaire->siret = $siret;
        return $this;
    }

    public function addDocument(Document $document): self
    {
        $this->gestionnaire->documents[] = $document;
        return $this;
    }

    public function build(): Gestionnaire
    {
        return $this->gestionnaire;
    }
}
