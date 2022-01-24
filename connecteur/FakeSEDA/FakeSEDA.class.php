<?php

class FakeSEDA extends SedaNG
{
    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        /* Nothing to do */
    }

    public function getBordereau(array $transactionsInfo)
    {
        return file_get_contents(__DIR__ . "/fixtures/bordereau.xml");
    }

    public function getBordereauNG(FluxData $fluxData): string
    {
        return $this->getBordereau([]);
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
