<?php

class RoleUtilisateurSQLTest extends PastellTestCase
{
    /**
     * @var RoleUtilisateur
     */
    private $roleUtilisateurSQL;

    protected function setUp()
    {
        parent::setUp();
        $this->roleUtilisateurSQL = new RoleUtilisateur(
            $this->getSQLQuery(),
            $this->getObjectInstancier()->getInstance(RoleSQL::class),
            //On utilise un cache !
            new StaticWrapper(),
            10
        );
    }

    public function testGetRole()
    {
        $role_list = $this->roleUtilisateurSQL->getRole(1);
        $this->assertEquals("admin", $role_list[0]['role']);
    }

    public function testgetAuthorizedRoleToDelegate()
    {
        $role_list = $this->roleUtilisateurSQL->getAuthorizedRoleToDelegate(1);
        $this->assertEquals("admin", $role_list[0]['role']);
    }

    public function testgetAuthorizedRoleToDelegateOtherRole()
    {
        $this->roleUtilisateurSQL->removeAllRole(2);
        $role_list = $this->roleUtilisateurSQL->getAuthorizedRoleToDelegate(2);
        foreach ($role_list as $role_info) {
            $result[] = $role_info['role'];
        }
        $this->assertFalse(isset($result['admin']));
    }

    public function testgetAllDroitEntite()
    {
        //FROM DATABASE
        $all = $this->roleUtilisateurSQL->getAllDroitEntite(2, 1);
        $this->assertDroit($all);
        //FROM CACHE
        $all = $this->roleUtilisateurSQL->getAllDroitEntite(2, 1);
        $this->assertDroit($all);
        //CLEANING CACHE
        $this->roleUtilisateurSQL->removeAllRolesEntite(2, 1);
        $all = $this->roleUtilisateurSQL->getAllDroitEntite(2, 1);
        $this->assertEmpty($all);
    }

    public function testAddRole()
    {
        $this->roleUtilisateurSQL->removeAllRole(2);
        $this->assertEquals(
            [],
            $this->roleUtilisateurSQL->getAllDroitEntite(2, 1)
        );
        $this->roleUtilisateurSQL->addRole(
            2,
            'admin',
            1
        );
        $all = $this->roleUtilisateurSQL->getAllDroitEntite(2, 1);
        $this->assertDroit($all);
    }


    public function testAddRoleAll()
    {
        $this->roleUtilisateurSQL->removeAllRole(2);

        $this->roleUtilisateurSQL->addRole(
            2,
            'admin',
            1
        );
        $all = $this->roleUtilisateurSQL->getAllDroit(2);
        $this->assertDroit($all);
    }


    private function assertDroit($all_droit)
    {
        $this->assertEquals([
            'actes-automatique:edition',
            'actes-automatique:lecture',
            'actes-generique:edition',
            'actes-generique:lecture',
            'actes-preversement-seda:edition',
            'actes-preversement-seda:lecture',
            'actes-reponse-prefecture:edition',
            'actes-reponse-prefecture:lecture',
            'annuaire:edition',
            'annuaire:lecture',
            'commande-generique:edition',
            'commande-generique:lecture',
            'connecteur:edition',
            'connecteur:lecture',
            'document-a-signer:edition',
            'document-a-signer:lecture',
            'entite:edition',
            'entite:lecture',
            'fournisseur-invitation:edition',
            'fournisseur-invitation:lecture',
            'helios-automatique:edition',
            'helios-automatique:lecture',
            'helios-generique:edition',
            'helios-generique:lecture',
            'journal:lecture',
            'mailsec-bidir:edition',
            'mailsec-bidir:lecture',
            'mailsec:edition',
            'mailsec:lecture',
            'message-service:edition',
            'message-service:lecture',
            'pdf-generique:edition',
            'pdf-generique:lecture',
            'role:edition',
            'role:lecture',
            'system:edition',
            'system:lecture',
            'test:edition',
            'test:lecture',
            'utilisateur:edition',
            'utilisateur:lecture',
        ], $all_droit);
    }

    public function testRoleNameIsTooLong()
    {
        //Ca bug si la taille maximum des champs utilisateur_role:role, role:role et role_droit:role n'est pas identique
        $roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);
        $role_id = "mon_super_role_qui_depasse_allegrement_les_soixante_quatre_caracteres";
        $roleSQL->edit($role_id, "Mon role très long");
        $roleSQL->addDroit($role_id, "foo:bar");

        $this->roleUtilisateurSQL->addRole(1, $role_id, 1);

        $this->assertTrue($this->roleUtilisateurSQL->hasDroit(1, "foo:bar", 1));
    }


    public function testGetArbreFille()
    {
        $entiteCreator = $this->getObjectInstancier()->getInstance(EntiteCreator::class);
        $id_e_1 = $entiteCreator->edit(0, "000000000", "Entité 1");
        $id_e_2 = $entiteCreator->edit(0, "000000000", "Entité 2");
        $id_e_3 = $entiteCreator->edit(0, "000000000", "Entité 3", Entite::TYPE_COLLECTIVITE, $id_e_2);

        $utilisateurCreator = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class);
        $id_u = $utilisateurCreator->create('test_get_arbre_fille', 'aa', 'aa', 'aa@aa.fr');

        $this->roleUtilisateurSQL->addRole($id_u, "admin", $id_e_1);
        $this->roleUtilisateurSQL->addRole($id_u, "admin", $id_e_3);

        $arbre_fille = $this->roleUtilisateurSQL->getArbreFille($id_u, "entite:lecture");
        //var_export($arbre_fille);

        $this->assertEquals(
            array (
                0 =>
                    array (
                        'id_e' => $id_e_1,
                        'denomination' => 'Entité 1',
                        'profondeur' => 0,
                    ),
                1 =>
                    array (
                        'id_e' => $id_e_3,
                        'denomination' => 'Entité 3',
                        'profondeur' => 0,
                    ),
            ),
            $arbre_fille
        );
    }

    public function testGetArbreFille2()
    {
        $entiteCreator = $this->getObjectInstancier()->getInstance(EntiteCreator::class);
        $id_e_1 = $entiteCreator->edit(0, "000000000", "Entité 1");
        $id_e_2 = $entiteCreator->edit(0, "000000000", "Entité 2");
        $id_e_3 = $entiteCreator->edit(0, "000000000", "Entité 3", Entite::TYPE_COLLECTIVITE, $id_e_2);

        $utilisateurCreator = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class);
        $id_u = $utilisateurCreator->create('test_get_arbre_fille', 'aa', 'aa', 'aa@aa.fr');

        $this->roleUtilisateurSQL->addRole($id_u, "admin", 0);

        $arbre_fille = $this->roleUtilisateurSQL->getArbreFille($id_u, "entite:lecture");

        $this->assertEquals(
            [
                0 =>
                    [
                        'id_e' => '1',
                        'denomination' => 'Bourg-en-Bresse',
                        'profondeur' => 0,
                    ],
                1 =>
                    [
                        'id_e' => '2',
                        'denomination' => 'CCAS',
                        'profondeur' => 1,
                    ],
                2 =>
                    [
                        'id_e' => '3',
                        'denomination' => 'Entité 1',
                        'profondeur' => 0,
                    ],
                3 =>
                    [
                        'id_e' => '4',
                        'denomination' => 'Entité 2',
                        'profondeur' => 0,
                    ],
                4 =>
                    [
                        'id_e' => '5',
                        'denomination' => 'Entité 3',
                        'profondeur' => 1,
                    ],
            ],
            $arbre_fille
        );
    }

    public function testGetChildrenWithPermission()
    {
        $entiteCreator = $this->getObjectInstancier()->getInstance(EntiteCreator::class);
        $id_e_2 = $entiteCreator->edit(0, "000000000", "Entité 2");
        $id_e_3 = $entiteCreator->edit(0, "000000000", "Entité 3", Entite::TYPE_COLLECTIVITE, $id_e_2);

        $utilisateurCreator = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class);
        $id_u = $utilisateurCreator->create('test', 'aa', 'aa', 'aa@aa.fr');

        $this->roleUtilisateurSQL->addRole($id_u, "admin", $id_e_2);
        $this->roleUtilisateurSQL->addRole($id_u, "admin", $id_e_3);

        $childrenWithPermissions = $this->roleUtilisateurSQL->getChildrenWithPermission($id_e_2, $id_u);

        $this->assertCount(1, $childrenWithPermissions);
        $this->assertSame($id_e_3, $childrenWithPermissions[0]['id_e']);
    }
}
