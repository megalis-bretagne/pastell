<?php

use Twig\Environment;

class FrontControllerTest extends PastellTestCase
{
    private FrontController $frontController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getObjectInstancier()->getInstance(Authentification::class)->connexion('admin', 1);
        $this->frontController = $this->getObjectInstancier()->getInstance(FrontController::class);
        $this->frontController->setTwigEnvironment($this->getObjectInstancier()->getInstance(Environment::class));
    }

    public function testDispatch()
    {
        $this->expectOutputRegex("#Liste des dossiers - Pastell#");
        $this->frontController->dispatch();
    }

    public function testDispatchBadController()
    {
        $this->expectOutputRegex("#HTTP/1.1 404 Not Found#");
        $this->frontController->setGetParameter([FrontController::PAGE_REQUEST => 'Foo/bar']);
        $this->frontController->dispatch();
    }

    public function testDispatchBadMethod()
    {
        $this->expectOutputRegex("#HTTP/1.1 404 Not Found#");
        $this->frontController->setGetParameter([FrontController::PAGE_REQUEST => 'Document/bar']);
        $this->frontController->dispatch();
    }

    public function testDispatchDefaultMethod()
    {
        $this->expectOutputRegex("#Liste des dossiers - Pastell#");
        $this->frontController->setGetParameter([FrontController::PAGE_REQUEST => 'Document']);
        $this->frontController->dispatch();
    }
}
