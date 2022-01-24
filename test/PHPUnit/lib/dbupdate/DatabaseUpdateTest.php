<?php

class DatabaseUpdateTest extends PastellTestCase
{
    public function testCreateSQL()
    {
        $databaseUpdate = new DatabaseUpdate(file_get_contents(PASTELL_PATH . "/installation/pastell.bin"), $this->getSQLQuery());
        $this->assertInternalType('array', $databaseUpdate->getAllSQLCommand());
    }

    public function testFileEmpty()
    {
        $databaseUpdate = new DatabaseUpdate(false, $this->getSQLQuery());
        $this->assertInternalType('array', $databaseUpdate->getAllSQLCommand());
    }

    public function testGetJson()
    {
        $databaseUpdate = new DatabaseUpdate(false, $this->getSQLQuery());
        $json = $databaseUpdate->getDatabaseDefinition();
        $result = json_decode($json, true);
        $this->assertInternalType('array', $result);
    }

    public function testWrite()
    {
        $databaseUpdate = new DatabaseUpdate(false, $this->getSQLQuery());
        $databaseUpdate->writeDefinition(
            $this->getObjectInstancier()->getInstance('workspacePath') . "/toto.bin",
            $this->getObjectInstancier()->getInstance('workspacePath') . "/toto.sql"
        );
        $content = file_get_contents($this->getObjectInstancier()->getInstance('workspacePath') . "/toto.bin");
        $result = json_decode($content, true);
        $this->assertInternalType('array', $result);
    }

    public function testGetDiff()
    {
        $databaseUpdate = new DatabaseUpdate(false, $this->getSQLQuery());
        $diff = $databaseUpdate->getDiff();
        $this->assertInternalType('array', $diff);
    }

    public function testLongtextTypeInsteadOfJsonType()
    {
        $databaseSchema = file_get_contents(PASTELL_PATH . '/installation/pastell.bin');
        $databaseUpdate = new DatabaseUpdate($databaseSchema, $this->getSQLQuery());

        $pastellSchema = json_decode($databaseSchema, true);
        $pastellSchema['type_dossier']['Column']['definition']['Type'] = 'longtext';
        $databaseUpdate->setDatabaseDefinition($pastellSchema);

        $this->assertEmpty($databaseUpdate->getDiff());
    }

    public function testUpdateFromJsonToLongtextIsDone()
    {
        $databaseSchema = file_get_contents(PASTELL_PATH . '/installation/pastell.bin');
        $pastellSchema = json_decode($databaseSchema, true);

        $pastellSchema['type_dossier']['Column']['definition']['Type'] = 'longtext';
        $databaseUpdate = new DatabaseUpdate(json_encode($pastellSchema), $this->getSQLQuery());

        $databaseUpdate->setDatabaseDefinition(json_decode($databaseSchema, true));

        $this->assertSame(
            [
                'ALTER TABLE `type_dossier` CHANGE `definition` `definition` longtext NOT NULL;'
            ],
            $databaseUpdate->getDiff()
        );
    }
}
