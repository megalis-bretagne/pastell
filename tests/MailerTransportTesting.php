<?php

declare(strict_types=1);

namespace Pastell\Tests;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class MailerTransportTesting extends AbstractTransport
{
    private SentMessage $message;

    protected function doSend(SentMessage $message): void
    {
        $this->message = $message;
    }

    public function __toString(): string
    {
        return '';
    }

    public function getSentMessage(): SentMessage
    {
        return $this->message;
    }
}
