<?php

namespace Pastell\Database;

use DatabaseUpdate;
use Extensions;
use Monolog\Logger;
use SQLQuery;

class DatabaseUpdater
{
    public const DATABASE_FILE = __DIR__ . '/../../installation/pastell.json';
    public const DATABASE_SQL_FILE = __DIR__ . '/../../installation/pastell.sql';

    public function __construct(
        private readonly SQLQuery $sqlQuery,
        private readonly ?Logger $logger = null,
        private readonly ?Extensions $extensions = null,
    ) {
    }

    /**
     * @throws \JsonException
     */
    private function getDatabaseDefinitionFromFile(string $filename): array
    {
        $databaseFileContent = file_get_contents($filename);
        return json_decode($databaseFileContent, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws \JsonException
     */
    public function getQueries(): array
    {
        $databaseDefinition = $this->getDatabaseDefinitionFromFile(self::DATABASE_FILE);
        if ($this->extensions) {
            $allComplementary = [];
            foreach ($this->extensions->getAllDatabaseFile() as $complementaryDatabaseFile) {
                $allComplementary[] = $this->getDatabaseDefinitionFromFile($complementaryDatabaseFile);
            }
            $databaseDefinition = array_merge($databaseDefinition, ...$allComplementary);
        }
        $databaseUpdate = new DatabaseUpdate(json_encode($databaseDefinition), $this->sqlQuery);
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
        $databaseUpdate->writeDefinition(self::DATABASE_FILE, self::DATABASE_SQL_FILE);
    }
}
