<?php

class FichierCleValeurTest extends PastellTestCase
{
    /**
     * @var string
     */
    private $filePath;

    protected function setUp()
    {
        parent::setUp();
        $this->filePath = $this->getObjectInstancier()->getInstance('workspacePath') . "/test.yml";
    }
    public function testGetNonExistentsValue()
    {
        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $this->assertFalse($fichierCleValeur->get("test1"));
    }

    public function testEmpty()
    {
        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $info = $fichierCleValeur->getInfo();
        $this->assertEmpty($info);
    }

    public function conservationString($string)
    {
        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $fichierCleValeur->set("test1", $string);
        $fichierCleValeur->save();

        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $this->assertEquals($string, $fichierCleValeur->get("test1"));
    }

    public function testEmptyValue()
    {
        $this->conservationString("");
    }

    public function testString()
    {
        $this->conservationString("test");
    }

    public function testPlus()
    {
        $this->conservationString("+");
    }

    public function testPlus2()
    {
        $this->conservationString("+2");
    }

    public function testPlus2A()
    {
        $this->conservationString("+2A");
    }

    public function testQuote()
    {
        $this->conservationString("'test'");
    }

    public function testDoubleQuote()
    {
        $this->conservationString('"test"');
    }

    public function testHash()
    {
        $this->conservationString("#ceci n'est pas un commentaire");
    }

    public function testReturn()
    {
        $this->conservationString("retour\nà la ligne");
    }

    public function testAnother()
    {
        $this->conservationString("#ceci n\'est pas un commentaire");
    }

    public function testTrue()
    {
        $this->conservationString("true");
    }

    public function testExists()
    {
        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $fichierCleValeur->set("test1", "premier");
        $fichierCleValeur->save();
        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $this->assertTrue($fichierCleValeur->exists("test1"));
        $this->assertFalse($fichierCleValeur->exists("test2"));
    }

    public function testDeuxObjet()
    {
        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $fichierCleValeur->set("test1", "premier");
        $fichierCleValeur->set("test2", "second");
        $fichierCleValeur->save();

        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $this->assertEquals("premier", $fichierCleValeur->get("test1"));
        $this->assertEquals("second", $fichierCleValeur->get("test2"));
    }

    public function testMulti0()
    {
        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $fichierCleValeur->setMulti("test1", "premier");
        $fichierCleValeur->save();

        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $this->assertEquals("premier", $fichierCleValeur->getMulti("test1"));
    }

    public function testMultiMany()
    {
        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $fichierCleValeur->setMulti("test1", "premier");
        $fichierCleValeur->setMulti("test1", "second", 1);
        $fichierCleValeur->save();

        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $this->assertEquals("premier", $fichierCleValeur->getMulti("test1"));
        $this->assertEquals("second", $fichierCleValeur->getMulti("test1", 1));
    }

    public function testAddValue()
    {
        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $fichierCleValeur->addValue("test1", "premier");
        $fichierCleValeur->addValue("test1", "second");
        $fichierCleValeur->save();

        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $this->assertEquals("premier", $fichierCleValeur->getMulti("test1"));
        $this->assertEquals("second", $fichierCleValeur->getMulti("test1", 1));
    }


    public function testCount()
    {
        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $fichierCleValeur->addValue("test1", "premier");
        $fichierCleValeur->addValue("test1", "troisieme");
        $fichierCleValeur->addValue("test1", "second");
        $this->assertEquals(3, $fichierCleValeur->count("test1"));
    }

    public function testDelete()
    {
        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $fichierCleValeur->addValue("test1", "premier");
        $fichierCleValeur->addValue("test1", "troisieme");
        $fichierCleValeur->addValue("test1", "second");
        $fichierCleValeur->delete("test1", 1);
        $fichierCleValeur->save();

        $fichierCleValeur = new FichierCleValeur($this->filePath);
        $this->assertEquals("premier", $fichierCleValeur->getMulti("test1"));
        $this->assertEquals("second", $fichierCleValeur->getMulti("test1", 1));
    }

    public function testUnescapeEmptyString()
    {
        file_put_contents($this->filePath, "test1: ");
        $fichierCleValeur = new FichierCleValeur($this->filePath, new YMLLoader(new MemoryCacheNone()));
        $this->assertEmpty($fichierCleValeur->get("test1"));
    }
}
