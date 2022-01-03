<?php

class ExtensionAPIControllerTest extends PastellTestCase
{
    public function testList()
    {
        $list = $this->getInternalAPI()->get("/extension");
        $this->assertEquals('/var/lib/pastell/pastell_cdg59', $list['result'][1]['path']);
    }

    public function testEdit()
    {
        $list = $this->getInternalAPI()->patch("/extension/1", array('path' => '/tmp'));
        $this->assertEquals('/tmp', $list['detail']['path']);
    }

    public function testEditPathNotFound()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le chemin « /foo/bar » n'existe pas sur le système de fichier");
        $this->getInternalAPI()->patch("/extension/1", array('path' => '/foo/bar'));
    }

    public function testEditExtensionNotFound()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Extension #42 non trouvée");
        $this->getInternalAPI()->patch("/extension/42", array('path' => '/tmp'));
    }

    public function testEditAlreadyExists()
    {
        $this->getInternalAPI()->post("/extension", array('path' => __DIR__ . '/../fixtures/extensions/extension-test'));
        $this->expectException("ConflictException");
        $this->expectExceptionMessage("L'extension #glaneur est déja présente");
        $this->getInternalAPI()->post("/extension", array('path' => __DIR__ . '/../fixtures/extensions/extension-test'));
    }

    public function testDeleteAction()
    {
        $this->getInternalAPI()->delete("/extension/1");
        $list = $this->getInternalAPI()->get("/extension");
        $this->assertArrayNotHasKey(1, $list['result']);
    }

    public function testDeleteActionNotFound()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Extension #42 non trouvée");
        $this->getInternalAPI()->delete("/extension/42");
    }

    public function testV1list()
    {
        $this->expectOutputRegex("#manifest#");
        $this->getV1("list-extension.php");
    }

    public function testV1create()
    {
        $this->expectOutputRegex("#manifest.yml absent#");
        $this->getV1("edit-extension.php?path=/tmp");
    }

    public function testV1edit()
    {
        $this->expectOutputRegex("#/tmp#");
        $this->getV1("edit-extension.php?path=/tmp&id_extension=1");
    }

    public function testV1delete()
    {
        $this->expectOutputRegex("#ok#");
        $this->getV1("delete-extension.php?id_extension=1");
    }

    public function testPatchAsEntiteAdministrator()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Acces interdit id_e=0, droit=system:edition,id_u=2');

        $this->getInternalAPIAsUser(2)->patch('/extension/1', ['path' => '/tmp']);
    }
}
