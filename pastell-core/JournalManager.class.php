<?php

class JournalManager
{
    private $journalSQL;
    private $journal_max_age_in_months;
    private $admin_email;

    /** @var Monolog\Logger */
    private $logger;

    public function __construct(
        Journal $journal,
        $journal_max_age_in_months,
        $admin_email,
        Monolog\Logger $logger
    ) {
        $this->journalSQL = $journal;
        $this->journal_max_age_in_months = $journal_max_age_in_months;
        $this->admin_email = $admin_email;
        $this->logger = $logger;
    }

    public function purgeToHistorique()
    {
        $this->logger->info("Lancement de la purge du journal des événements");
        try {
            $this->journalSQL->purgeToHistorique($this->journal_max_age_in_months);
        } catch (Exception $e) {
            mail_wrapper($this->admin_email, "Problème sur la purge du journal", $e->getMessage());
            $this->logger->error("Problème sur la purge du journal" . $e->getMessage());
            return false;
        }
        $this->logger->info("Purge du journal des événements terminée");
        return true;
    }
}
