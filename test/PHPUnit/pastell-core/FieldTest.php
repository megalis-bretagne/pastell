<?php

class FieldTest extends PHPUnit\Framework\TestCase
{
    public function testUnaccent()
    {
        $this->assertEquals("Eric", Field::unaccent("Éric"));
    }

    public function testCanonicalize()
    {
        $this->assertEquals("eric", Field::Canonicalize("Éric"));
    }
}
