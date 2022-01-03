<?php

class FluxAPIControllerTest extends PastellTestCase
{
    public function testListAction()
    {
        $list = $this->getInternalAPI()->get("/flux");
        $this->assertEquals('Mail sécurisé', $list['mailsec']['nom']);
    }

    public function testInfoAction()
    {
        $info = $this->getInternalAPI()->get("/flux/test");
        $this->assertEquals('test1', $info['test1']['name']);
    }

    public function testActionList()
    {
        $info = $this->getInternalAPI()->get("/flux/test/action");
        $this->assertEquals('Test', $info['test']['action-class']);
    }

    public function testInfoActionNotExists()
    {
        $this->expectException("NotFoundException");
        $this->expectExceptionMessage("Le flux foo n'existe pas sur cette plateforme");
        $this->getInternalAPI()->get("/flux/foo");
    }

    public function testListActionNotExists()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le flux foo n'existe pas sur cette plateforme");
        $this->getInternalAPI()->get("/flux/foo/action");
    }

    public function testV1()
    {
        $this->expectOutputRegex("#mailsec#");
        $this->getV1("document-type.php");
    }

    public function testV1Detail()
    {
        $this->expectOutputRegex("#Destinataire#");
        $this->getV1("document-type-info.php?type=mailsec");
    }

    public function testV1Action()
    {
        $this->expectOutputRegex("#reception-partielle#");
        $this->getV1("document-type-action.php?type=mailsec");
    }
}
