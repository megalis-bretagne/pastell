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
        $this->assertEquals(array(
            0 => 'actes-automatique:edition',
            1 => 'actes-automatique:lecture',
            2 => 'actes-generique:edition',
            3 => 'actes-generique:lecture',
            4 => 'actes-preversement-seda:edition',
            5 => 'actes-preversement-seda:lecture',
            6 => 'annuaire:edition',
            7 => 'annuaire:lecture',
            8 => 'document-a-signer:edition',
            9 => 'document-a-signer:lecture',
            10 => 'entite:edition',
            11 => 'entite:lecture',
            12 => 'fournisseur-invitation:edition',
            13 => 'fournisseur-invitation:lecture',
            14 => 'helios-automatique:edition',
            15 => 'helios-automatique:lecture',
            16 => 'helios-generique:edition',
            17 => 'helios-generique:lecture',
            18 => 'journal:lecture',
            19 => 'mailsec:edition',
            20 => 'mailsec:lecture',
            21 => 'message-service:edition',
            22 => 'message-service:lecture',
            23 => 'pdf-generique:edition',
            24 => 'pdf-generique:lecture',
            25 => 'role:edition',
            26 => 'role:lecture',
            27 => 'system:edition',
            28 => 'system:lecture',
            29 => 'test:edition',
            30 => 'test:lecture',
            31 => 'utilisateur:edition',
            32 => 'utilisateur:lecture',
        ), $all_droit);
	}

	public function testRoleNameIsTooLong(){
		//Ca bug si la taille maximum des champs utilisateur_role:role, role:role et role_droit:role n'est pas identique
		$roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);
		$role_id = "mon_super_role_qui_depasse_allegrement_les_soixante_quatre_caracteres";
		$roleSQL->edit($role_id,"Mon role très long");
		$roleSQL->addDroit($role_id,"foo:bar");

		$this->roleUtilisateurSQL->addRole(1,$role_id,1);

		$this->assertTrue($this->roleUtilisateurSQL->hasDroit(1,"foo:bar",1));
	}


}