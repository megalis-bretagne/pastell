<?php

use PHPUnit\Framework\TestCase;

class RecuperateurPastellTest extends TestCase
{
    public function testRecupSimpe()
    {
        $recup = new Recuperateur(['toto' => 'titi']);
        $this->assertEquals("titi", $recup->get('toto'));
        $this->assertEquals(false, $recup->get('valeur inexistante'));
        $this->assertEquals("titi", $recup->get('valeur inexistante', 'titi'));
    }

    public function testTableau()
    {
        $value = [3,45,32];
        $tab = ['toto' => $value];
        $recup = new Recuperateur($tab);
        $this->assertEquals($value, $recup->get('toto'));
    }

    public function testGetInt()
    {
        $tab = ['toto' => 'test'];
        $recup = new Recuperateur($tab);
        $this->assertEquals(0, $recup->getInt('toto'));
    }

    public function testGetTrim()
    {
        $tab = ['toto' => ' test '];
        $recup = new Recuperateur($tab);
        $this->assertEquals('test', $recup->get('toto'));
    }

    public function testGetNoTrim()
    {
        $tab = ['toto' => ' test '];
        $recup = new Recuperateur($tab);
        $this->assertEquals(' test ', $recup->getNoTrim('toto'));
    }

    public function testGetNoTrimDefault()
    {
        $tab = ['toto' => ' test '];
        $recup = new Recuperateur($tab);
        $this->assertEquals(false, $recup->getNoTrim('titi'));
    }

    public function testGetAll()
    {
        $tab = ['a' => 1,'b' => 42];
        $recup = new Recuperateur($tab);
        $all = $recup->getAll();
        $this->assertEquals($tab, $all);
    }
}
