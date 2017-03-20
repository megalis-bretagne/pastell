<?php

class RoleUtilisateurSQLTest extends PastellTestCase {

	/**
	 * @var RoleUtilisateur
	 */
	private $roleUtilisateurSQL;

	protected function setUp(){
		parent::setUp();
		$this->roleUtilisateurSQL = $this->getObjectInstancier()->getInstance('RoleUtilisateur');
	}

	public function testGetRole(){
		$role_list = $this->roleUtilisateurSQL->getRole(1);
		$this->assertEquals("admin",$role_list[0]['role']);
	}

	public function testgetAuthorizedRoleToDelegate(){
		$role_list = $this->roleUtilisateurSQL->getAuthorizedRoleToDelegate(1);
		$this->assertEquals("admin",$role_list[0]['role']);
	}

	public function testgetAuthorizedRoleToDelegateOtherRole(){
		$this->roleUtilisateurSQL->removeAllRole(2);
		$role_list = $this->roleUtilisateurSQL->getAuthorizedRoleToDelegate(2);
		foreach($role_list as $role_info){
			$result[] = $role_info['role'];
		}
		$this->assertFalse(isset($result['admin']));
	}
}