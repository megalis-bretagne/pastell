<?php

class InternalAPITest extends PastellTestCase
{
    public function testGetVersion()
    {
        $version_info = $this->getInternalAPI()->get("/version");
        $this->assertEquals("1.4-fixtures", $version_info['version']);
    }

    public function testUnauhtenticated()
    {
        $internalAPI = $this->getInternalAPI();
        $internalAPI->setUtilisateurId(false);
        $this->expectException("UnauthorizedException");
        $this->expectExceptionMessage("Vous devez être connecté pour utiliser l'API");
        $internalAPI->get("/version");
    }

    public function testScriptTest()
    {
        $internalAPI = $this->getInternalAPI();
        $internalAPI->setUtilisateurId(false);
        $internalAPI->setCallerType(InternalAPI::CALLER_TYPE_SCRIPT);
        $version_info = $internalAPI->get("/version");
        $this->assertEquals("1.4-fixtures", $version_info['version']);
    }

    public function testRessourceAbsente()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Ressource absente");
        $this->getInternalAPI()->get("");
    }

    public function testNotExistingRessource()
    {
        $this->expectException("NotFoundException");
        $this->expectExceptionMessage("La ressource Foo n'a pas été trouvée");
        $this->getInternalAPI()->get("/foo");
    }
}
