<?php

namespace Pastell\Database;

use DatabaseUpdate;
use Monolog\Logger;
use SQLQuery;

class DatabaseUpdater
{
    public const DATABASE_FILE = __DIR__ . '/../../installation/pastell.bin';
    public const DATABASE_SQL_FILE = __DIR__ . '/../../installation/pastell.sql';

    public function __construct(
        private readonly SQLQuery $sqlQuery,
        private readonly ?Logger $logger = null,
    ) {
    }

    public function getQueries(): array
    {
        $databaseUpdate = new DatabaseUpdate(file_get_contents(self::DATABASE_FILE), $this->sqlQuery);
        return $databaseUpdate->getDiff();
    }

    /**
     * @throws \Exception
     */
    public function update(): void
    {
        foreach ($this->getQueries() as $query) {
            $this->sqlQuery->query($query);
            $this->logger?->info("[UPDATE DATABASE] $query");
        }
    }

    public function updateDefinitionFromDatabase(): void
    {
        $databaseUpdate = new DatabaseUpdate(false, $this->sqlQuery);
        $databaseUpdate->writeDefinition(DatabaseUpdater::DATABASE_FILE, self::DATABASE_SQL_FILE);
    }
}
