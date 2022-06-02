<?php

declare(strict_types=1);

namespace Pastell\Tests;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class MailerTransportTesting extends AbstractTransport
{
    /**
     * @var SentMessage[]
     */
    private array $messages;

    protected function doSend(SentMessage $messages): void
    {
        $this->messages[] = $messages;
    }

    public function __toString(): string
    {
        return '';
    }

    public function getSentMessage(): SentMessage
    {
        return $this->messages[0];
    }

    public function getAllSentMessages(): array
    {
        return $this->messages;
    }
}
