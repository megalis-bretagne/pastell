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
    private array $message;

    protected function doSend(SentMessage $message): void
    {
        $this->message[] = $message;
    }

    public function __toString(): string
    {
        return '';
    }

    public function getSentMessage(): SentMessage
    {
        return $this->message[0];
    }

    public function getAllSentMessages(): array
    {
        return $this->message;
    }
}
