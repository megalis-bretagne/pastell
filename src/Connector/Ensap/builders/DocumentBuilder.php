<?php

namespace Pastell\Connector\Ensap\builders;

use Pastell\Connector\Ensap\parts\Document;

class DocumentBuilder
{
    private const THEME = 8;
    private const SSTHEME = 45;
    private Document $document;

    public function __construct()
    {
        $this->document = new Document();
    }

    public function setTheme(int $theme): self
    {
        $this->document->theme = $theme;
        return $this;
    }

    public function setSstheme(int $sstheme): self
    {
        $this->document->sstheme = $sstheme;
        return $this;
    }

    public function setDateDocument(string $dateDocument): self
    {
        $this->document->dateDocument = $dateDocument;
        return $this;
    }

    public function setMontant(?string $montant): self
    {
        $this->document->montant = $montant;
        return $this;
    }

    public function setNomFichier(string $nomFichier): self
    {
        $this->document->nomFichier = $nomFichier;
        return $this;
    }

    public function build(): Document
    {
        $this->document->theme = self::THEME;
        $this->document->sstheme = self::SSTHEME;
        return $this->document;
    }
}
