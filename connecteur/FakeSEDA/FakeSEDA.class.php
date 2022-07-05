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
        if ($this->donneesFormulaire->get('seda_bordereau_generation_response') === 'error') {
            throw new \RuntimeException('FakeSEDA: Invalid bordereau');
        }
        return true;
    }

    public function getLastValidationError(): array
    {
        if ($this->donneesFormulaire->get('seda_bordereau_generation_response') === 'error') {
            $error1 = new LibXMLError();
            $error1->message = 'FakeSEDA: Error 1';
            return [$error1];
        }
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
