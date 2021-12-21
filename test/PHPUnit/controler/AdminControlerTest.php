<?php

class AdminControlerTest extends ControlerTestCase
{
    /** @var  AdminControler */
    private $adminControler;

    protected function setUp()
    {
        parent::setUp();
        $this->adminControler = $this->getControlerInstance(AdminControler::class);
    }

    public function testCreateAdmin()
    {
        $this->assertTrue($this->adminControler->createAdmin('admin2', 'admin', 'admin@sigmalis.com'));
    }

    public function testCreateAdminFail()
    {
        $this->assertFalse($this->adminControler->createAdmin('admin', 'admin', 'admin@sigmalis.com'));
    }
}
