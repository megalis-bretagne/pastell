<?php

namespace Pastell\System\Check;

use DatabaseUpdate;
use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;
use SQLQuery;

class DatabaseSchemaCheck implements CheckInterface
{
    /**
     * @var SQLQuery
     */
    private $sqlQuery;
    /**
     * @var string
     */
    private $database_file;

    public function __construct(SQLQuery $sqlQuery, string $database_file)
    {
        $this->sqlQuery = $sqlQuery;
        $this->database_file = $database_file;
    }

    public function check(): array
    {
        return [$this->checkDatabaseSchema()];
    }

    private function checkDatabaseSchema(): HealthCheckItem
    {
        $databaseUpdate = new DatabaseUpdate(file_get_contents($this->database_file), $this->sqlQuery);
        $databaseSqlCommand = $databaseUpdate->getDiff();
        $databaseSchemaResult = "Le schéma de la base est conforme au schéma attendu par le code.";

        if ($databaseSqlCommand) {
            $databaseSchemaResult = "Le schéma de la base n'est pas conforme au schéma attendu par le code !" .
                implode(',', $databaseSqlCommand);
        }
        return (new HealthCheckItem('Schéma de la base de données', $databaseSchemaResult))
            ->setSuccess(!(bool)$databaseSqlCommand);
    }
}
