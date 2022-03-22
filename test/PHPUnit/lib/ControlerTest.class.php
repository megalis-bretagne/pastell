<?php

class ControlerTest extends PHPUnit\Framework\TestCase
{
    /** @var  ObjectInstancier */
    private $objectInstancier;

    /** @var  Controler */
    private $controler;

    protected function setUp(): void
    {
        $this->objectInstancier = new ObjectInstancier();
        $this->controler = $this->getMockForAbstractClass("Controler", [$this->objectInstancier]);
    }

    public function testSetDontRedirect()
    {
        $this->controler->setDontRedirect(true);
        $this->assertTrue($this->controler->isDontRedirect());
    }

    public function testMagicMethod()
    {
        $this->controler->{'foo'} = 'bar';
        $this->assertEquals('bar', $this->controler->{'foo'});
    }

    public function testMagicMethodFromObjectInstancier()
    {
        $this->objectInstancier->setInstance('foo', 'bar');
        $this->assertEquals('bar', $this->controler->{'foo'});
    }

    public function testSetAllViewParameter()
    {
        $this->controler->setAllViewParameter(['foo' => 'bar']);
        $this->assertEquals('bar', $this->controler->getViewParameter()['foo']);
    }

    public function testIsViewParameter()
    {
        $this->controler->{'foo'} = 'bar';
        $this->assertTrue($this->controler->isViewParameter('foo'));
    }

    public function testRedirect()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Exit called with code 0");
        $this->expectOutputRegex("#Location: .*foo#");
        $this->controler->redirect("foo");
    }

    public function testRender()
    {
        $this->objectInstancier->setInstance('template_path', __DIR__ . "/fixtures/");
        $this->expectOutputString("OK");
        $this->controler->render("template");
    }

    public function testRenderDefault()
    {
        $this->objectInstancier->setInstance('template_path', __DIR__ . "/fixtures/");
        $this->controler->template_milieu = "template";
        $this->expectOutputString("OK");
        $this->controler->renderDefault();
    }

    public function testGetLastError()
    {
        $this->assertEmpty($this->controler->getLastError());
    }
}
