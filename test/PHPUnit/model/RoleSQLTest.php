<?php

class RoleSQLTest extends PastellTestCase
{
    /** @var  RoleSQL */
    private $roleSQL;

    private $role_droit = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleSQL = new RoleSQL($this->getSQLQuery());
        $this->createRole('role1', 'Rôle 1', ['entite:edition','entite:lecture','test:lecture','test:edition']);
        $this->createRole('role2', 'Rôle 2', ['entite:edition','entite:lecture']);
    }

    private function createRole($id, $libelle, array $droit_list)
    {
        $this->roleSQL->edit($id, $libelle);
        $this->role_droit[$id] = $droit_list;
        $this->roleSQL->updateDroit($id, $droit_list);
    }

    public function testGetRoleWithDroitRoleInferieur()
    {
        $this->assertContains('role2', $this->roleSQL->getAuthorizedRoleToDelegate($this->role_droit['role2']));
        $this->assertNotContains('role1', $this->roleSQL->getAuthorizedRoleToDelegate($this->role_droit['role2']));
    }

    public function testGetRoleWithDroitRoleSuperieur()
    {
        $this->assertContains('role2', $this->roleSQL->getAuthorizedRoleToDelegate($this->role_droit['role1']));
        $this->assertContains('role1', $this->roleSQL->getAuthorizedRoleToDelegate($this->role_droit['role1']));
    }
}
