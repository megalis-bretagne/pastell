<?php

declare(strict_types=1);

use Pastell\Mailer\Mailer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class JournalManager
{
    public function __construct(
        private readonly Journal $journalSQL,
        private readonly int $journal_max_age_in_months,
        private readonly string $admin_email,
        private readonly Monolog\Logger $logger,
        private readonly Mailer $mailer,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function purgeToHistorique(): bool
    {
        $this->logger->info('Lancement de la purge du journal des événements');
        try {
            $this->journalSQL->purgeToHistorique($this->journal_max_age_in_months);
        } catch (Exception $e) {
            $message = sprintf('Erreur sur la purge du journal : %s', $e->getMessage());
            $this->logger->error($message);
            $templatedEmail = (new TemplatedEmail())
                ->to($this->admin_email)
                ->subject('[PASTELL] Problème sur la purge du journal')
                ->text($message);
            $this->mailer->send($templatedEmail);
            return false;
        }
        $this->logger->info('Purge du journal des événements terminée');
        return true;
    }
}
