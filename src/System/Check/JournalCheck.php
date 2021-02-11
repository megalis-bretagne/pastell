<?php

namespace Pastell\System\Check;

use Journal;
use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;

class JournalCheck implements CheckInterface
{
    /**
     * @var Journal
     */
    private $journal;

    public function __construct(Journal $journal)
    {
        $this->journal = $journal;
    }

    public function check(): array
    {
        $firstLineDate = round((time() - strtotime($this->journal->getFirstLineDate())) / 86400);
        return [
            new HealthCheckItem(
                "Nombre d'enregistrements dans la table journal",
                number_format_fr($this->journal->getNbLine())
            ),
            new HealthCheckItem(
                "Nombre d'enregistrements dans la table journal_historique",
                number_format_fr($this->journal->getNbLineHistorique())
            ),
            new HealthCheckItem(
                'Date du premier enregistrement de la table journal',
                $this->journal->getFirstLineDate()
            ),
            new HealthCheckItem("Nombre de mois de conservation du journal", JOURNAL_MAX_AGE_IN_MONTHS),
            (new HealthCheckItem(
                "Age du premier enregistrement de la table journal",
                $firstLineDate . ' jours'
            ))->setSuccess($firstLineDate <= JOURNAL_MAX_AGE_IN_MONTHS * 31),
        ];
    }
}
