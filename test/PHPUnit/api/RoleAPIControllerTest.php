<?php

class RoleAPIControllerTest extends PastellTestCase
{
    public function testList()
    {
        $list = $this->getInternalAPI()->get("/role");
        $this->assertEquals('admin', $list[0]['role']);
    }

    public function testListFailed()
    {
        $internalAPI = $this->getInternalAPI();
        $internalAPI->setUtilisateurId(42);
        $this->expectException("Exception");
        $this->expectExceptionMessage("Vous devez avoir le droit role:lecture pour accéder à la ressource.");
        $internalAPI->get("/role");
    }

    public function testV1()
    {
        $this->expectOutputRegex("#Administrateur#");
        $this->getV1("list-roles.php");
    }
}
