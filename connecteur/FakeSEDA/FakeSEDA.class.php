<?php

class FakeSEDA extends SEDAConnecteur
{
    private DonneesFormulaire $donneesFormulaire;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->donneesFormulaire = $donneesFormulaire;
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

    /**
     * @throws UnrecoverableException
     */
    public function generateArchive(FluxData $fluxData, string $archive_path): void
    {
        if ($this->donneesFormulaire->get('seda_archive_generation_response') === 'error') {
            throw new UnrecoverableException('FakeSEDA: Erreur provoquée par le simulateur');
        }
        touch($archive_path);
    }
}
