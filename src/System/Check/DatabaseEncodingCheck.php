<?php

namespace Pastell\System\Check;

use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;
use SQLQuery;

class DatabaseEncodingCheck implements CheckInterface
{
    /**
     * @var SQLQuery
     */
    private $sqlQuery;

    public function __construct(SQLQuery $sqlQuery)
    {
        $this->sqlQuery = $sqlQuery;
    }

    public function check(): array
    {
        return [$this->checkDatabaseEncoding()];
    }

    private function checkDatabaseEncoding(): HealthCheckItem
    {
        $tablesCollection = $this->sqlQuery->getTablesCollation();
        $databaseEncoding = "L'encodage de la base est conforme à l'encodage attendu.";
        $success = true;
        $details = [];
        if (count($tablesCollection) > 1) {
            $databaseEncoding = "Les tables n'utilisent pas toutes le même encodage !";
            $success = false;
            foreach ($tablesCollection as $encoding => $tableList) {
                $details[] = new HealthCheckItem($encoding, implode(', ', $tableList));
            }
        } elseif (array_keys($tablesCollection)[0] !== SQLQuery::PREFERRED_TABLE_COLLATION) {
            $databaseEncoding = sprintf(
                "L'encodage trouvé %s ne correspond pas à l'encodage prévu %s",
                array_keys($tablesCollection)[0],
                SQLQuery::PREFERRED_TABLE_COLLATION
            );
            $success = false;
        }

        return (new HealthCheckItem('Encodage de la base de données', $databaseEncoding))
            ->setSuccess($success)
            ->setDetails($details);
    }
}
