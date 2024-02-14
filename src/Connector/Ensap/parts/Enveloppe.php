<?php

declare(strict_types=1);

namespace Pastell\Connector\Ensap\parts;

class Enveloppe
{
    public Message $message;
    public Emetteur $emetteur;
    public array $assures;
}
