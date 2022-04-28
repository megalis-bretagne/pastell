<?php

class FakeSEDA extends SEDAConnecteur
{
    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        /* Nothing to do */
    }

    public function getBordereau(FluxData $fluxData): string
    {
        return file_get_contents(__DIR__ . '/fixtures/bordereau.xml');
    }

    public function validateBordereau(string $bordereau): bool
    {
        return true;
    }

    public function getLastValidationError()
    {
        return [];
    }

    public function generateArchive(FluxData $fluxData, string $archive_path): void
    {
        touch($archive_path);
    }
}
