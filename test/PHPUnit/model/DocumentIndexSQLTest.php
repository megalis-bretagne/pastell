<?php

class DocumentIndexSQLTest extends PastellTestCase
{
    /**
     * @var DocumentIndexSQL
     */
    private $documentIndexSQL;

    protected function setUp(): void
    {
        parent::setUp();
        $this->documentIndexSQL = $this->getObjectInstancier()->getInstance(DocumentIndexSQL::class);
        $this->documentIndexSQL->index('FOO', "bar", "baz");
    }

    public function testIndex()
    {
        $this->assertEquals("baz", $this->documentIndexSQL->get('FOO', "bar"));
    }

    public function testReIndex()
    {
        $this->documentIndexSQL->index('FOO', "bar", "baz2");
        $this->assertEquals("baz2", $this->documentIndexSQL->get('FOO', "bar"));
    }

    public function testGetByFieldValue()
    {
        $this->assertEquals("FOO", $this->documentIndexSQL->getByFieldValue("bar", "baz"));
    }

    public function testSaveBigField()
    {
        $value = str_repeat("01234567890", 20);
        $this->documentIndexSQL->index('long', "my_big_field_value", $value);
        $this->assertEquals(
            substr($value, 0, DocumentIndexSQL::FIELD_VALUE_LENGTH),
            $this->documentIndexSQL->get('long', "my_big_field_value")
        );
        $this->assertEquals(
            "long",
            $this->documentIndexSQL->getByFieldValue('my_big_field_value', $value)
        );
    }

    public function testSaveBigFieldName()
    {
        $name = str_repeat("01234567890", 20);
        $this->documentIndexSQL->index('long', $name, "my_value");
        $this->assertEquals(
            "my_value",
            $this->documentIndexSQL->get('long', $name)
        );
        $this->assertEquals(
            "long",
            $this->documentIndexSQL->getByFieldValue($name, "my_value")
        );
    }
}
