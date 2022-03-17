<?php

declare(strict_types=1);

class FakeGED extends GEDConnecteur
{
    public function send(DonneesFormulaire $donneesFormulaire): array
    {
        return ['fake-document' => 'fake-id'];
    }
}
