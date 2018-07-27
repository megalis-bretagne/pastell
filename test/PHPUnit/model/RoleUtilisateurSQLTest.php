<?php

class RoleUtilisateurSQLTest extends PastellTestCase {

	/**
	 * @var RoleUtilisateur
	 */
	private $roleUtilisateurSQL;

	protected function setUp(){
		parent::setUp();
		$this->roleUtilisateurSQL = new RoleUtilisateur(
			$this->getSQLQuery(),
			$this->getObjectInstancier()->getInstance(RoleSQL::class),
			//On utilise un cache !
			new StaticWrapper(),
			10
		);

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

	public function testgetAllDroitEntite(){
		//FROM DATABASE
		$all = $this->roleUtilisateurSQL->getAllDroitEntite(2,1);
		$this->assertDroit($all);
		//FROM CACHE
		$all = $this->roleUtilisateurSQL->getAllDroitEntite(2,1);
		$this->assertDroit($all);
		//CLEANING CACHE
		$this->roleUtilisateurSQL->removeAllRolesEntite(2,1);
		$all = $this->roleUtilisateurSQL->getAllDroitEntite(2,1);
		$this->assertEmpty($all);
	}

	public function testAddRole(){
		$this->roleUtilisateurSQL->removeAllRole(2);
		$this->assertEquals(
			[],
			$this->roleUtilisateurSQL->getAllDroitEntite(2,1)
		);
		$this->roleUtilisateurSQL->addRole(
			2,'admin',1
		);
		$all = $this->roleUtilisateurSQL->getAllDroitEntite(2,1);
		$this->assertDroit($all);
	}


	public function testAddRoleAll(){
		$this->roleUtilisateurSQL->removeAllRole(2);

		$this->roleUtilisateurSQL->addRole(
			2,'admin',1
		);
		$all = $this->roleUtilisateurSQL->getAllDroit(2);
		$this->assertDroit($all);
	}


	private function assertDroit($all_droit){
		$this->assertEquals(array (
			0 => 'actes-automatique:edition',
			1 => 'actes-automatique:lecture',
			2 => 'actes-generique:edition',
			3 => 'actes-generique:lecture',
			4 => 'actes-preversement-seda:edition',
			5 => 'actes-preversement-seda:lecture',
			6 => 'annuaire:edition',
			7 => 'annuaire:lecture',
			8 => 'entite:edition',
			9 => 'entite:lecture',
			10 => 'fournisseur-invitation:edition',
			11 => 'fournisseur-invitation:lecture',
			12 => 'helios-automatique:edition',
			13 => 'helios-automatique:lecture',
			14 => 'helios-generique:edition',
			15 => 'helios-generique:lecture',
			16 => 'journal:lecture',
			17 => 'mailsec:edition',
			18 => 'mailsec:lecture',
			19 => 'message-service:edition',
			20 => 'message-service:lecture',
			21 => 'role:edition',
			22 => 'role:lecture',
			23 => 'system:edition',
			24 => 'system:lecture',
			25 => 'test:edition',
			26 => 'test:lecture',
			27 => 'utilisateur:edition',
			28 => 'utilisateur:lecture',
		),$all_droit);
	}


}