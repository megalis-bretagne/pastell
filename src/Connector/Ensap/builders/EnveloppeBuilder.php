<?php

declare(strict_types=1);

namespace Pastell\Connector\Ensap\builders;

use Pastell\Connector\Ensap\parts\Assure;
use Pastell\Connector\Ensap\parts\Emetteur;
use Pastell\Connector\Ensap\parts\Enveloppe;
use Pastell\Connector\Ensap\parts\Message;

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

    public function getEnveloppe(array $enveloppeData): Enveloppe
    {
        $this->setMessage($enveloppeData['message'])
            ->setEmetteur($enveloppeData['emetteur']);
        foreach ($enveloppeData['assures'] as $assure) {
            $this->addAssure($assure);
        }
        return $this->build();
    }
}
