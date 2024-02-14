<?php

namespace Pastell\Connector\Ensap\builders;

use Pastell\Connector\Ensap\parts\Message;

class MessageBuilder
{
    private const NATURE_FLUX = 'ENVOI-BP-GENERIQUE';
    private const VERSION_FICHIER = '01.00';
    private Message $message;

    public function __construct()
    {
        $this->message = new Message();
    }

    public function setNomFichier(string $nomFichier): self
    {
        $this->message->nomFichier = $nomFichier;
        return $this;
    }

    public function setDateTraitement(string $dateTraitement): self
    {
        $this->message->dateTraitement = $dateTraitement;
        return $this;
    }

    public function build(): Message
    {
        $this->message->natureFlux = self::NATURE_FLUX;
        $this->message->versionFichier = self::VERSION_FICHIER;
        return $this->message;
    }
}