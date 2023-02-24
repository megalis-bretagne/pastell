<?php

declare(strict_types=1);

abstract class GEDConnecteur extends Connecteur
{
    protected DonneesFormulaire $connecteurConfig;

    private array $gedDocumentsId;

    abstract public function send(DonneesFormulaire $donneesFormulaire): array;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    public function getGedDocumentsId(): array
    {
        return $this->gedDocumentsId ?? [];
    }

    public function addGedDocumentId($filename, $fileId): void
    {
        $this->gedDocumentsId[$filename] = $fileId;
    }
}
