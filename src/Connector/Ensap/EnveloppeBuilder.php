<?php

declare(strict_types=1);

namespace Pastell\Connector\Ensap;

use Pastell\Connector\Ensap\enveloppe\Assure;
use Pastell\Connector\Ensap\enveloppe\Emetteur;
use Pastell\Connector\Ensap\enveloppe\Enveloppe;
use Pastell\Connector\Ensap\enveloppe\Message;

class EnveloppeBuilder
{
    private Enveloppe $enveloppe;

    public function __construct()
    {
        $this->enveloppe = new Enveloppe();
    }

    public function setMessage(Message $message): self
    {
        $this->enveloppe->message = $message;
        return $this;
    }

    public function setEmetteur(Emetteur $emetteur): self
    {
        $this->enveloppe->emetteur = $emetteur;
        return $this;
    }

    public function addAssure(Assure $assure): self
    {
        $this->enveloppe->assures[] = $assure;
        return $this;
    }

    public function build(): Enveloppe
    {
        return $this->enveloppe;
    }
}
