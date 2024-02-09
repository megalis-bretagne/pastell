<?php

declare(strict_types=1);

abstract class CFNConnecteur extends Connecteur
{
    protected DonneesFormulaire $connecteurConfig;

    abstract public function send(array $bp_files, string $xml, DonneesFormulaire $donneesFormulaire): void;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire): void
    {
        $this->connecteurConfig = $donneesFormulaire;
    }
}
