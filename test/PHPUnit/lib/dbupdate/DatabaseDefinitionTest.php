<?php

require_once(PASTELL_PATH . "/lib/dbupdate/DatabaseDefinition.class.php");

class DatabaseDefinitionTest extends PastellTestCase
{
    public function testGetDefinition()
    {
        $databaseDefinition = new DatabaseDefinition($this->getSQLQuery());
        $this->assertEquals("id_u", $databaseDefinition->getDefinition()['utilisateur']['Column']['id_u']['Field']);
    }
}
