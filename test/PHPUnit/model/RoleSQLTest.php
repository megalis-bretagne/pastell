<?php

class RoleSQLTest extends PastellTestCase
{

    /** @var  RoleSQL */
    private $roleSQL;

    private $role_droit = array();

    protected function setUp()
    {
        parent::setUp();
        $this->roleSQL = new RoleSQL($this->getSQLQuery());
        $this->createRole('role1', 'Rôle 1', array('entite:edition','entite:lecture','test:lecture','test:edition'));
        $this->createRole('role2', 'Rôle 2', array('entite:edition','entite:lecture'));
    }

    private function createRole($id, $libelle, array $droit_list)
    {
        $this->roleSQL->edit($id, $libelle);
        $this->role_droit[$id] = $droit_list;
        $this->roleSQL->updateDroit($id, $droit_list);
    }

    public function testGetRoleWithDroitRoleInferieur()
    {
        $this->assertTrue(in_array('role2', $this->roleSQL->getAuthorizedRoleToDelegate($this->role_droit['role2'])));
        $this->assertTrue(! in_array('role1', $this->roleSQL->getAuthorizedRoleToDelegate($this->role_droit['role2'])));
    }

    public function testGetRoleWithDroitRoleSuperieur()
    {
        $this->assertTrue(in_array('role2', $this->roleSQL->getAuthorizedRoleToDelegate($this->role_droit['role1'])));
        $this->assertTrue(in_array('role1', $this->roleSQL->getAuthorizedRoleToDelegate($this->role_droit['role1'])));
    }
}
