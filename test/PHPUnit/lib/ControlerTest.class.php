<?php

declare(strict_types=1);

class ControlerTest extends PHPUnit\Framework\TestCase
{
    private ObjectInstancier $objectInstancier;
    private Controler $controler;

    protected function setUp(): void
    {
        $this->objectInstancier = new ObjectInstancier();
        $this->controler = new Controler($this->objectInstancier);
    }

    public function testSetDontRedirect()
    {
        $this->controler->setDontRedirect(true);
        $this->assertTrue($this->controler->isDontRedirect());
    }

    public function testSetAllViewParameter()
    {
        $this->controler->setAllViewParameter(['foo' => 'bar']);
        $this->assertEquals('bar', $this->controler->getViewParameter()['foo']);
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
        $this->expectOutputString("OK\n");
        $this->controler->render("template");
    }

    public function testRenderDefault()
    {
        $this->objectInstancier->setInstance('template_path', __DIR__ . "/fixtures/");
        $this->controler->setViewParameter('template_milieu', 'template');
        $this->expectOutputString("OK\n");
        $this->controler->renderDefault();
    }
}
