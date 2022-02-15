<?php

class RoleControlerTest extends ControlerTestCase
{
    /** @var  RoleControler */
    private $roleControler;

    protected function setUp()
    {
        parent::setUp();
        $this->roleControler = $this->getControlerInstance("RoleControler");
    }

    public function testIndexAction()
    {
        $this->expectOutputRegex("##");
        $this->roleControler->indexAction();
    }

    public function testDetailAction()
    {
        $this->expectOutputRegex("##");
        $this->roleControler->detailAction();
    }

    public function testEditionAction()
    {
        $this->expectOutputRegex("##");
        $this->roleControler->editionAction();
    }

    public function testEditionAction2()
    {
        $this->expectOutputRegex("##");
        $_GET = array('role' => 'admin');
        $this->roleControler->editionAction();
    }

    public function testDoEditionAction()
    {
        $this->expectException("LastMessageException");
        $this->setPostInfo(array('role' => 'test','libelle' => 'test'));
        $this->roleControler->doEditionAction();
    }

    public function testDoDeleteAction()
    {
        $this->expectException("LastMessageException");
        $this->roleControler->doDeleteAction();
    }

    public function testDoDetailAction()
    {
        $this->expectException("LastMessageException");
        $this->setPostInfo(array('role' => 'test','droit' => array('system:lecture' => 'selected')));
        $this->roleControler->doDetailAction();
    }
}
