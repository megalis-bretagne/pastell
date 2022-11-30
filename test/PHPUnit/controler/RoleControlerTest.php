<?php

class RoleControlerTest extends ControlerTestCase
{
    /** @var  RoleControler */
    private $roleControler;

    protected function setUp(): void
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
        $_GET = ['role' => 'admin'];
        $this->roleControler->editionAction();
    }

    public function testDoEditionAction()
    {
        $this->expectException("LastMessageException");
        $this->setPostInfo(['role' => 'test','libelle' => 'test']);
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
        $this->setPostInfo(['role' => 'test','droit' => ['system:lecture' => 'selected']]);
        $this->roleControler->doDetailAction();
    }

    public function testDoEditionActionNewRole() {
        $this->setPostInfo(
            [
                'role' => 'test',
                'libelle' => 'test',
                'nouveau' => true
            ]
        );

        try {
            $this->roleControler->doEditionAction();
        } catch (LastMessageException $e) {
            /** Nothing to do */
        }

        $this->assertEquals(['entite:lecture' => 1, 'journal:lecture' => 1], $this->roleControler->getRoleSQL()->getDroit([], 'test'));
    }
}
