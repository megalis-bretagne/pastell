<?php

class FormulaireTest extends PHPUnit\Framework\TestCase
{
    public function testGetField()
    {
        $formulaire = new Formulaire(["onglet1" => false]);
        $this->assertFalse($formulaire->getField("bar"));
    }

    public function testGetFieldInOnglet()
    {
        $formulaire = new Formulaire(["onglet1" => false]);
        $this->assertFalse($formulaire->getField("foo", "onglet2"));
    }

    public function testGetFieldOk()
    {
        $formulaire = new Formulaire(["onglet1" => ["foo" => []]]);
        $this->assertInstanceOf("Field", $formulaire->getField("foo"));
    }

    public function testGetFieldOngletOk()
    {
        $formulaire = new Formulaire(["onglet1" => ["foo" => []]]);
        $this->assertInstanceOf("Field", $formulaire->getField("foo", "onglet1"));
    }
}
