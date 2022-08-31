<?php

namespace Pastell\System\Check;

use Pastell\Database\DatabaseUpdater;
use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;

class DatabaseSchemaCheck implements CheckInterface
{
    public function __construct(
        private readonly DatabaseUpdater $databaseUpdater,
    ) {
    }

    public function check(): array
    {
        return [$this->checkDatabaseSchema()];
    }

    private function checkDatabaseSchema(): HealthCheckItem
    {
        try {
            $databaseSqlCommand = $this->databaseUpdater->getQueries();
        } catch (\UnrecoverableException $e) {
            $databaseSchemaResult = $e->getMessage();
            return (new HealthCheckItem('Schéma de la base de données', $databaseSchemaResult))
                ->setSuccess(false);
        }
        $databaseSchemaResult = 'Le schéma de la base est conforme au schéma attendu par le code.';

        if ($databaseSqlCommand) {
            $databaseSchemaResult = "Le schéma de la base n'est pas conforme au schéma attendu par le code !" .
                implode(',', $databaseSqlCommand);
        }
        return (new HealthCheckItem('Schéma de la base de données', $databaseSchemaResult))
            ->setSuccess(!$databaseSqlCommand);
    }
}
