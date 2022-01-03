<?php

class FakeGED extends GEDConnecteur
{
    public function send(DonneesFormulaire $donneesFormulaire)
    {
        return ['fake-document' => 'fake-id'];
    }
}
