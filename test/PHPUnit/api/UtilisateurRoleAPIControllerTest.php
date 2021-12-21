<?php

class UtilisateurRoleAPIControllerTest extends PastellTestCase
{
    public function testList()
    {
        $list = $this->getInternalAPI()->get("utilisateur/1/role");
        $this->assertEquals('admin', $list[0]['role']);
    }

    public function testAdd()
    {
        $this->getInternalAPI()->post("utilisateur/1/role", array('id_e' => 0,'role' => 'utilisateur'));
        $list = $this->getInternalAPI()->get("utilisateur/1/role");
        $this->assertEquals('utilisateur', $list[1]['role']);
    }

    public function testAddBadUtilisateur()
    {
        $this->expectException("NotFoundException");
        $this->expectExceptionMessage("L'utilisateur n'existe pas : {id_u=42}");
        $this->getInternalAPI()->post("utilisateur/42/role", array('id_e' => 0,'role' => 'utilisateur'));
    }

    public function testAddBadRole()
    {
        $this->expectException("NotFoundException");
        $this->expectExceptionMessage("Le role spécifié n'existe pas {role=foo}");
        $this->getInternalAPI()->post("utilisateur/1/role", array('id_e' => 0,'role' => 'foo'));
    }

    public function testAddSeveral()
    {
        $this->expectOutputRegex("#ok#");
        $this->getV1("add-several-role-utilisateur.php?id_u=2&id_e=1&deleteRoles=true&role[]=utilisateur&role[]=autre");
        $list = $this->getInternalAPI()->get("utilisateur/2/role");
        $this->assertEquals('autre', $list[0]['role']);
    }

    public function testAddSeveralNoRole()
    {
        $this->expectOutputRegex('#\[#');
        $this->getV1("add-several-role-utilisateur.php?id_u=2&id_e=1");
    }

    public function testAddSeveralOneRole()
    {
        $this->expectOutputRegex("#ok#");
        $this->getV1("add-several-role-utilisateur.php?id_u=2&id_e=1&deleteRoles=true&role=autre");
        $list = $this->getInternalAPI()->get("utilisateur/2/role");
        $this->assertEquals('autre', $list[0]['role']);
    }


    public function testDelete()
    {
        $this->getInternalAPI()->delete("utilisateur/2/role?id_e=1&role=admin");
        $list = $this->getInternalAPI()->get("utilisateur/2/role");
        $this->assertEquals('aucun droit', $list[0]['role']);
    }

    public function testDeleteSeveral()
    {
        $this->expectOutputRegex("#ok#");
        $this->getV1("delete-several-roles-utilisateur.php?id_u=2&id_e=1&role[]=admin");
        $list = $this->getInternalAPI()->get("utilisateur/2/role");
        $this->assertEquals('aucun droit', $list[0]['role']);
    }

    public function testDeleteSevaralNoRole()
    {
        $this->expectOutputRegex('#\[#');
        $this->getV1("delete-several-roles-utilisateur.php?id_u=2&id_e=1");
    }

    public function testDeleteSeveralOneRole()
    {
        $this->expectOutputRegex("#ok#");
        $this->getV1("delete-several-roles-utilisateur.php?id_u=2&id_e=1&role=admin");
        $list = $this->getInternalAPI()->get("utilisateur/2/role");
        $this->assertEquals('aucun droit', $list[0]['role']);
    }
}
