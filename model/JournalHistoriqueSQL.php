<?php

class JournalHistoriqueSQL extends SQL
{
    public function getCount(): int
    {
        $sql = "SELECT count(*) FROM journal_historique";
        return $this->queryOne($sql);
    }

    public function getFirstDate()
    {
        $sql = "SELECT MIN(date) FROM journal_historique";
        return $this->queryOne($sql);
    }

    public function getLastDate()
    {
        $sql = "SELECT MAX(date) FROM journal_historique";
        return $this->queryOne($sql);
    }


    public function truncate(): void
    {
        $sql = "TRUNCATE TABLE journal_historique;";
        $this->query($sql);
    }
}
