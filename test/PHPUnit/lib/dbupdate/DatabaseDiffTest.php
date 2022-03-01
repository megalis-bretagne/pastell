<?php

class DatabaseDiffTest extends PHPUnit\Framework\TestCase
{
    private function getDatabaseDefinitionArray()
    {
        return array(
            "utilisateur" => array(
                "Engine" => "MyISAM",
                "Column" => array(
                    "id_u" => array(
                        "Field" => "id_u",
                        "Type" => "int(11)",
                        "Null" => "NO",
                        "Key" => "PRI",
                        "Default" => "",
                        "Extra" => "auto_increment"
                    )
                ),
                "Index" => array(
                    "PRIMARY" => array(
                        "type" => "BTREE",
                        "col" => array("id_u"),
                        "unique" => 1
                    )
                )
            )
        );
    }

    public function testAddTable()
    {
        $databaseDiff = new DatabaseDiff();
        $diff = $databaseDiff->getDiff($this->getDatabaseDefinitionArray(), array());
        $this->assertMatchesRegularExpression("#CREATE TABLE `utilisateur`#", $diff[0]);
    }

    public function testDropTable()
    {
        $databaseDiff = new DatabaseDiff();
        $diff = $databaseDiff->getDiff(array(), $this->getDatabaseDefinitionArray());
        $this->assertMatchesRegularExpression("#DROP TABLE `utilisateur`#", $diff[0]);
    }

    public function testAddColumn()
    {
        $databaseDiff = new DatabaseDiff();
        $db2 = $this->getDatabaseDefinitionArray();
        $db1 = $db2;
        $db1["utilisateur"]["Column"]["nom"] = array(
            "Field" => "nom",
            "Type" => "varchar(256)",
            "Null" => "NO",
            "Key" => "",
            "Default" => "",
            "Extra" => ""
        );
        $diff = $databaseDiff->getDiff($db1, $db2);
        $this->assertEquals("ALTER TABLE `utilisateur` ADD `nom` varchar(256) NOT NULL DEFAULT '';", $diff[0]);
    }

    public function testDropColumn()
    {
        $databaseDiff = new DatabaseDiff();
        $db2 = $this->getDatabaseDefinitionArray();
        $db1 = $db2;
        $db1["utilisateur"]["Column"]["nom"] = array(
            "Field" => "nom",
            "Type" => "varchar(256)",
            "Null" => "NO",
            "Key" => "",
            "Default" => "",
            "Extra" => ""
        );
        $diff = $databaseDiff->getDiff($db2, $db1);
        $this->assertEquals("ALTER TABLE `utilisateur` DROP `nom`;", $diff[0]);
    }

    public function testChangeEngine()
    {
        $databaseDiff = new DatabaseDiff();
        $db2 = $this->getDatabaseDefinitionArray();
        $db1 = $db2;
        $db1["utilisateur"]["Engine"] = "InnoDB";
        $diff = $databaseDiff->getDiff($db1, $db2);
        $this->assertEquals("ALTER TABLE `utilisateur` ENGINE = InnoDB;", $diff[0]);
    }

    public function testOnChangeColumn()
    {
        $databaseDiff = new DatabaseDiff();
        $db2 = $this->getDatabaseDefinitionArray();
        $db1 = $db2;
        $db1["utilisateur"]["Column"]["id_u"]["Type"] = "varchar(256)";
        $diff = $databaseDiff->getDiff($db1, $db2);
        $this->assertEquals("ALTER TABLE `utilisateur` CHANGE `id_u` `id_u` varchar(256) NOT NULL DEFAULT '' AUTO_INCREMENT;", $diff[0]);
    }

    public function testOnAddIndex()
    {
        $databaseDiff = new DatabaseDiff();
        $db2 = $this->getDatabaseDefinitionArray();
        $db2["utilisateur"]["Column"]["nom"] = array(
            "Field" => "nom",
            "Type" => "varchar(256)",
            "Null" => "NO",
            "Key" => "",
            "Default" => "",
            "Extra" => ""
        );
        $db1 = $db2;
        $db1['utilisateur']['Index']["toto"] = array(
            "type" => "BTREE",
            "col" => array("nom"),
            "unique" => 1
        );
        $diff = $databaseDiff->getDiff($db1, $db2);
        $this->assertEquals("CREATE  UNIQUE INDEX `toto` ON `utilisateur` (`nom`) ;", $diff[0]);
    }

    public function testOnAddFullTextIndex()
    {
        $databaseDiff = new DatabaseDiff();
        $db2 = $this->getDatabaseDefinitionArray();
        $db2["utilisateur"]["Column"]["nom"] = array(
            "Field" => "nom",
            "Type" => "varchar(256)",
            "Null" => "NO",
            "Key" => "",
            "Default" => "",
            "Extra" => ""
        );
        $db1 = $db2;
        $db1['utilisateur']['Index']["toto"] = array(
            "type" => "FULLTEXT",
            "col" => array("nom"),
            "unique" => 0
        );
        $diff = $databaseDiff->getDiff($db1, $db2);
        $this->assertEquals("CREATE  FULLTEXT INDEX `toto` ON `utilisateur` (`nom`) ;", $diff[0]);
    }

    public function testOnDropIndex()
    {
        $databaseDiff = new DatabaseDiff();
        $db2 = $this->getDatabaseDefinitionArray();
        $db2["utilisateur"]["Column"]["nom"] = array(
            "Field" => "nom",
            "Type" => "varchar(256)",
            "Null" => "NO",
            "Key" => "",
            "Default" => "",
            "Extra" => ""
        );
        $db1 = $db2;
        $db1['utilisateur']['Index']["toto"] = array(
            "type" => "BTREE",
            "col" => array("nom"),
            "unique" => 1
        );
        $diff = $databaseDiff->getDiff($db2, $db1);
        $this->assertEquals("DROP INDEX `toto` ON `utilisateur`;", $diff[0]);
    }

    public function testOnChangeIndexName()
    {
        $databaseDiff = new DatabaseDiff();
        $db2 = $this->getDatabaseDefinitionArray();
        $db2["utilisateur"]["Column"]["nom"] = array(
            "Field" => "nom",
            "Type" => "varchar(256)",
            "Null" => "NO",
            "Key" => "",
            "Default" => "",
            "Extra" => ""
        );
        $db2['utilisateur']['Index']["toto"] = array(
            "type" => "BTREE",
            "col" => array("nom"),
            "unique" => 1
        );
        $db1 = $db2;
        unset($db1["utilisateur"]['Index']['toto']);
        $db1["utilisateur"]["Index"]["titi"] = $db2['utilisateur']['Index']["toto"];
        $diff = $databaseDiff->getDiff($db2, $db1);
        $this->assertEquals("DROP INDEX `titi` ON `utilisateur`;", $diff[0]);
        $this->assertEquals("CREATE  UNIQUE INDEX `toto` ON `utilisateur` (`nom`) ;", $diff[1]);
    }

    public function testAddPrimaryKey()
    {
        $databaseDiff = new DatabaseDiff();
        $db2 = $this->getDatabaseDefinitionArray();
        $db1 = $db2;
        unset($db1["utilisateur"]["Index"]["PRIMARY"]);
        unset($db1["utilisateur"]["Column"]["id_u"]);
        $diff = $databaseDiff->getDiff($db2, $db1);
        $this->assertEquals("ALTER TABLE `utilisateur` ADD `id_u` int(11) NOT NULL DEFAULT '' AUTO_INCREMENT PRIMARY KEY ;", $diff[0]);
        $this->assertEquals("CREATE  UNIQUE INDEX PRIMARY ON `utilisateur` (`id_u`) ;", $diff[1]);
    }


    public function testAddKey()
    {
        $databaseDiff = new DatabaseDiff();
        $db2 = $this->getDatabaseDefinitionArray();
        $db2["utilisateur"]["Index"]["ma_cle"] = $db2["utilisateur"]["Index"]["PRIMARY"];
        $db2["utilisateur"]["Index"]["ma_cle"]["type"] = "HASH";
        $db2["utilisateur"]["Column"]["id_u"]["Extra"]  = "";
        $db2["utilisateur"]["Column"]["id_u"]["Key"]  = "ma_cle";
        unset($db2["utilisateur"]["Index"]["PRIMARY"]);
        $db1 = $db2;
        unset($db1["utilisateur"]);

        $diff = $databaseDiff->getDiff($db2, $db1);

        $this->assertMatchesRegularExpression("#HASH UNIQUE KEY `ma_cle`#", $diff[0]);
    }

    public function testAddDefaultNow()
    {
        $databaseDiff = new DatabaseDiff();
        $db2 = $this->getDatabaseDefinitionArray();
        $db1 = $db2;
        $db1["utilisateur"]["Column"]["nom"] = array(
            "Field" => "nom",
            "Type" => "timestamp",
            "Null" => "NO",
            "Key" => "",
            "Default" => "now()",
            "Extra" => ""
        );
        $diff = $databaseDiff->getDiff($db1, $db2);
        $this->assertEquals("ALTER TABLE `utilisateur` ADD `nom` timestamp NOT NULL DEFAULT now();", $diff[0]);
    }
}
