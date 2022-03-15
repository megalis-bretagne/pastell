<?php

declare(strict_types=1);

use JetBrains\PhpStorm\ArrayShape;

class FakeGED extends GEDConnecteur
{
    #[ArrayShape(['fake-document' => 'string'])] public function send(DonneesFormulaire $donneesFormulaire): array
    {
        return ['fake-document' => 'fake-id'];
    }
}
