<?php

class ConnecteurTypeActionExecutorTest extends PHPUnit\Framework\TestCase
{
    /** @var ConnecteurTypeActionExecutor $connecteurTypeActionExecutor */
    private $connecteurTypeActionExecutor;

    protected function setUp(): void
    {
        $this->connecteurTypeActionExecutor = $this->getMockForAbstractClass("ConnecteurTypeActionExecutor", [new ObjectInstancier()]);
    }

    public function testMapping()
    {
        $this->connecteurTypeActionExecutor->setMapping(["foo" => "bar"]);
        $this->assertEquals("bar", $this->connecteurTypeActionExecutor->getMappingValue("foo"));
    }

    public function testMappingDefaultValue()
    {
        $this->connecteurTypeActionExecutor->setMapping(["foo" => "bar"]);
        $this->assertEquals("baz", $this->connecteurTypeActionExecutor->getMappingValue("baz"));
    }
}
