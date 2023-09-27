<?php

class FakeGED extends GEDConnecteur
{
    private string $ged_envoi_status;
    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire): void
    {
        $this->ged_envoi_status = $donneesFormulaire->get('ged_envoi_status', '');
    }

    /**
     * @throws UnrecoverableException
     */
    public function send(DonneesFormulaire $donneesFormulaire): array
    {
        if ($this->ged_envoi_status === 'error') {
            throw new UnrecoverableException(
                "Erreur irrécupérable déclenchée par le connecteur fake ged (ged_envoi_status configuré à 'error')"
            );
        }
        return ['fake-document' => 'fake-id'];
    }
}
