<?php

use Pastell\Mailer\Mailer;
use Pastell\Tests\MailerTransportTesting;

trait MailerTransportTestingTrait
{
    private MailerTransportTesting $mailerTransportTesting;

    final public function setMailerTransportForTesting(): void
    {
        $this->mailerTransportTesting = new MailerTransportTesting();
        $mailer = new \Symfony\Component\Mailer\Mailer($this->mailerTransportTesting);
        $pastellMailer = $this->getObjectInstancier()->getInstance(Mailer::class);
        $pastellMailer->setMailer($mailer);
    }

    final public function assertMessageContainsString(string $expectedString): void
    {
        self::assertStringContainsString(
            $expectedString,
            $this->mailerTransportTesting->getSentMessage()->getMessage()->toString()
        );
    }
}
