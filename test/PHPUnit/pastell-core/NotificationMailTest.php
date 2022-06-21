<?php

declare(strict_types=1);

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class NotificationMailTest extends PastellTestCase
{
    use MailerTransportTestingTrait;

    /**
     * @throws TransportExceptionInterface
     */
    public function testSendMail(): void
    {
        $this->setMailerTransportForTesting();
        $notification = $this->getObjectInstancier()->getInstance(Notification::class);
        $notification->add(1, 1, 'actes-generique', 'send-ged', false);
        $id_d = $this->createDocument('test')['id_d'];
        $notificationMail = $this->getObjectInstancier()->getInstance(NotificationMail::class);
        $notificationMail->notify(1, $id_d, 'send-ged', 'actes-generique', 'foo');
        $this->assertMessageContainsString('vous envoie la notification suivante');
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testSendDailyDigest(): void
    {
        $this->setMailerTransportForTesting();
        $notification = $this->getObjectInstancier()->getInstance(Notification::class);
        $notification->add(1, 1, 'actes-generique', 'send-ged', true);
        $id_d = $this->createDocument('test')['id_d'];
        $notificationMail = $this->getObjectInstancier()->getInstance(NotificationMail::class);
        $notificationMail->notify(1, $id_d, 'send-ged', 'actes-generique', 'foo');
        $notificationMail->sendDailyDigest();
        $this->assertMessageContainsString('journalier sur certains types de');
    }
}
