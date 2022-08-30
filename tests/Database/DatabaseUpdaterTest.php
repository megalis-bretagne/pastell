<?php

namespace Pastell\Tests\Database;

use Pastell\Database\DatabaseUpdater;
use PastellTestCase;
use UnrecoverableException;

class DatabaseUpdaterTest extends PastellTestCase
{
    public function testGetQueries(): void
    {
        $databaseUpdater = $this->getObjectInstancier()->getInstance(DatabaseUpdater::class);
        self::assertEmpty($databaseUpdater->getQueries());
    }

    public function testGetQueriesWithExtension(): void
    {
        $extensionLoader = $this->getObjectInstancier()->getInstance(\ExtensionLoader::class);
        $extensionLoader->loadExtension([ __DIR__ . '/fixtures/extension']);
        $databaseUpdater = $this->getObjectInstancier()->getInstance(DatabaseUpdater::class);
        self::assertEquals(
            [
                0 => 'CREATE TABLE `table_test` (
	`id` int(11) NOT NULL,
	`column_test` varchar(16) NOT NULL
)  ENGINE=MyISAM  ;',
            ],
            $databaseUpdater->getQueries()
        );
    }

    public function testCantUpdatePastellTables()
    {
        $extensionLoader = $this->getObjectInstancier()->getInstance(\ExtensionLoader::class);
        $extensionLoader->loadExtension([ __DIR__ . '/fixtures/bad-extension']);
        $databaseUpdater = $this->getObjectInstancier()->getInstance(DatabaseUpdater::class);
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessageMatches(
            '#Le fichier .*/bad-extension/database.json contient la définition de la table utilisateur déjà présente dans Pastell !#'
        );
        $databaseUpdater->getQueries();
    }
}
