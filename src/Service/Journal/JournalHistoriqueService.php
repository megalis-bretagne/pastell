<?php

namespace Pastell\Service\Journal;

use Journal;
use JournalHistoriqueSQL;

class JournalHistoriqueService
{
    private $journalHistoriqueSQL;
    private $journal;

    public function __construct(
        JournalHistoriqueSQL $journalHistoriqueSQL,
        Journal $journal
    ) {
        $this->journalHistoriqueSQL = $journalHistoriqueSQL;
        $this->journal = $journal;
    }

    public function truncate()
    {
        $count = $this->journalHistoriqueSQL->getCount();
        $min_date = $this->journalHistoriqueSQL->getFirstDate();
        $max_date = $this->journalHistoriqueSQL->getLastDate();
        if ($count <= 0) {
            return;
        }
        $this->journalHistoriqueSQL->truncate();

        $message = sprintf(
            "Purge de la table journal_historique : %d enregistrement(s) supprimé(s), enregistrement le plus agé : %s, enregistrement le plus récent : %s",
            $count,
            $min_date,
            $max_date
        );

        $this->journal->addSQL(
            Journal::JOURNAL,
            0,
            0,
            '',
            '',
            $message
        );
    }
}
