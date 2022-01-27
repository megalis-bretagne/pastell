<?php

use Pastell\Service\Connecteur\ConnecteurActionService;

class ConnecteurAPIControllerTest extends PastellTestCase
{
    public function testListAction()
    {
        $list = $this->getInternalAPI()->get("/entite/0/connecteur");
        $this->assertEquals('horodateur-interne', $list[0]['id_connecteur']);
    }

    public function testGetBadEntiteConnecteur()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le connecteur 12 n'appartient pas à l'entité 2");
        $this->getInternalAPI()->get("/entite/2/connecteur/12");
    }

    public function testGetBadEntite()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("L'entité 42 n'existe pas");
        $this->getInternalAPI()->get("/entite/42/connecteur");
    }

    public function testCreate()
    {
        $info = $this->getInternalAPI()->post(
            "/entite/1/connecteur",
            ['libelle' => 'Connecteur de test','id_connecteur' => 'test']
        );
        $this->assertEquals('Connecteur de test', $info['libelle']);

        $connecteurActionService = $this->getObjectInstancier()->getInstance(ConnecteurActionService::class);
        $connecteur_action_message = $connecteurActionService->getByIdCe($info['id_ce'])[0]['message'];
        $this->assertEquals("Le connecteur test « Connecteur de test » a été créé", $connecteur_action_message);
    }

    public function testCreateWithoutLibelle()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le libellé est obligatoire.");
        $this->getInternalAPI()->post(
            "/entite/1/connecteur",
            ['libelle' => '','id_connecteur' => 'test']
        );
    }

    public function testCreateGlobale()
    {
        $info = $this->getInternalAPI()->post(
            "/entite/0/connecteur",
            ['libelle' => 'Test','id_connecteur' => 'test']
        );
        $this->assertEquals(0, $info['id_e']);
    }

    public function testCreateNotExist()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Aucun connecteur du type « foo »");
        $this->getInternalAPI()->post(
            "/entite/1/connecteur",
            ['libelle' => 'Connecteur de test','id_connecteur' => 'foo']
        );
    }

    public function testDelete()
    {
        $info = $this->getInternalAPI()->delete("/entite/1/connecteur/12");
        $this->assertEquals("ok", $info['result']);
    }

    public function testDeleteNotExist()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Ce connecteur n'existe pas.");
        $this->getInternalAPI()->delete("/entite/1/connecteur/42");
    }

    public function testDeleteUsed()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Ce connecteur est utilisé par des flux :  actes-generique");
        $this->getInternalAPI()->delete("/entite/1/connecteur/1");
    }

    public function testEdit()
    {
        $info = $this->getInternalAPI()->post(
            "/entite/1/connecteur",
            ['libelle' => 'Connecteur de test','id_connecteur' => 'test']
        );
        $this->assertEquals('Connecteur de test', $info['libelle']);
        $id_ce = $info['id_ce'];
        $info = $this->getInternalAPI()->patch("/entite/1/connecteur/$id_ce", ['libelle' => 'bar']);
        $this->assertEquals('bar', $info['libelle']);

        $connecteurActionService = $this->getObjectInstancier()->getInstance(ConnecteurActionService::class);
        $connecteur_action_message = $connecteurActionService->getByIdCe($id_ce)[0]['message'];
        $this->assertEquals("Le libellé a été modifié en « bar »", $connecteur_action_message);
    }

    public function testEditNotExist()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Ce connecteur n'existe pas.");
        $this->getInternalAPI()->patch("/entite/1/connecteur/42", ['libelle' => 'bar']);
    }

    public function testEditNotLibelle()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le libellé est obligatoire.");
        $this->getInternalAPI()->patch("/entite/1/connecteur/12", ['libelle' => '']);
    }

    public function testEditContentAction()
    {
        $info = $this->getInternalAPI()->patch("/entite/1/connecteur/12/content", ['champs1' => 'foo']);
        $this->assertEquals('foo', $info['data']['champs1']);
        $id_ce = $info['id_ce'];
        $connecteurActionService = $this->getObjectInstancier()->getInstance(ConnecteurActionService::class);
        $connecteur_action_message = $connecteurActionService->getByIdCe($id_ce)[0]['message'];
        $this->assertEquals("Modification du connecteur via l'API", $connecteur_action_message);
    }

    public function testEditContentOnChangeAction()
    {
        $info = $this->getInternalAPI()->patch("/entite/1/connecteur/12/content", ['champs3' => 'foo']);
        $this->assertEquals('foo', $info['data']['champs4']);
    }

    public function testPostFile()
    {
        $result = $this->getInternalAPI()->post(
            "/entite/1/connecteur/12/file/champs5",
            [
                'file_name' => 'test.txt',
                'file_content' => 'test...'
            ]
        );
        $this->assertEquals("test.txt", $result['data']['champs5'][0]);
        $this->expectOutputRegex("#test...#");
        $this->expectException("Exception");
        $this->expectExceptionMessage("Exit called with code 0");
        $this->getInternalAPI()->get("/entite/1/connecteur/12/file/champs5");
    }

    public function testAction()
    {
        $result = $this->getInternalAPI()->post("/entite/1/connecteur/12/action/ok");
        $this->assertEquals(['result' => 1,'last_message' => 'OK !'], $result);
    }

    public function testActionBadConnecteurID()
    {
        $internalAPI = $this->getInternalAPI();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Ce connecteur n'existe pas.");
        $internalAPI->post("/entite/1/connecteur/foo/action/ok");
    }

    public function testActionForbiddenAction()
    {
        $internalAPI = $this->getInternalAPI();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("L'action « not_possible »  n'est pas permise : role_id_e n'est pas vérifiée");
        $internalAPI->post("/entite/1/connecteur/12/action/not_possible");
    }

    public function testActionBadActionName()
    {
        $internalAPI = $this->getInternalAPI();
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("L'action foo n'existe pas");
        $internalAPI->post("/entite/1/connecteur/12/action/foo");
    }

    /**
     * @throws Exception
     */
    public function testGetConnecteur()
    {
        $id_ce = $this->createConnector('iParapheur', "Connecteur i-Parapheur")['id_ce'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);

        $donneesFormulaire->setTabData([
            'iparapheur_wsdl' => 'https://iparapheur.test',
            'iparapheur_login' => 'admin@pastell',
            'iparapheur_password' => 'Xoo7kiey',
            'iparapheur_type' => 'PES',
            'not_existing_element' => "I don't exist"

        ]);
        $info = $this->getInternalAPI()->get("/entite/1/connecteur/$id_ce");
        $this->assertEquals([
            'iparapheur_wsdl' => 'https://iparapheur.test',
            'iparapheur_login' => 'admin@pastell',
            'iparapheur_password' => 'MOT DE PASSE NON RECUPERABLE',
            'iparapheur_type' => 'PES',
            'not_existing_element' => "I don't exist"
        ], $info['data']);
    }

    public function testGetContentWithoutRight()
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class);
        $id_u = $utilisateurSQL->create("badguy", "foo", "foo", "test@bar.baz");

        $roleUtilisateur = $this->getObjectInstancier()->getInstance(RoleUtilisateur::class);
        $roleUtilisateur->addRole($id_u, "admin", 2);

        $internalAPI = $this->getInternalAPI();
        $internalAPI->setUtilisateurId($id_u);

        try {
            $internalAPI->patch("/entite/2/connecteur/12/content", ['champs1' => 'bar']);
        } catch (Exception $e) {
/* Nothing to do  */
        }

        $internalAPI->setUtilisateurId(1);
        $result = $internalAPI->get("/entite/1/connecteur/12/");
        $this->assertEmpty($result['data']);
    }

    public function testActionWithoutRight()
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class);
        $id_u = $utilisateurSQL->create("badguy", "foo", "foo", "test@bar.baz");

        $roleUtilisateur = $this->getObjectInstancier()->getInstance(RoleUtilisateur::class);
        $roleUtilisateur->addRole($id_u, "admin", 2);

        $internalAPI = $this->getInternalAPI();
        $internalAPI->setUtilisateurId($id_u);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le connecteur 12 n'appartient pas à l'entité 2");
        $internalAPI->post("/entite/2/connecteur/12/action/ok");
    }

    public function testGetAll()
    {
        $this->assertSame(
            [
                [
                    'id_ce' => '1',
                    'id_e' => '1',
                    'libelle' => 'Fake iParapheur',
                    'id_connecteur' => 'fakeIparapheur',
                    'type' => 'signature',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => 'Bourg-en-Bresse',
                ],
                [
                    'id_ce' => '2',
                    'id_e' => '1',
                    'libelle' => 'Fake Tdt',
                    'id_connecteur' => 'fakeTdt',
                    'type' => 'TdT',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => 'Bourg-en-Bresse',
                ],
                [
                    'id_ce' => '3',
                    'id_e' => '1',
                    'libelle' => 'SEDA Standard',
                    'id_connecteur' => 'actes-seda-standard',
                    'type' => 'Bordereau SEDA',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => 'Bourg-en-Bresse',
                ],
                [
                    'id_ce' => '4',
                    'id_e' => '1',
                    'libelle' => 'Fake SAE',
                    'id_connecteur' => 'fakeSAE',
                    'type' => 'SAE',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => 'Bourg-en-Bresse',
                ],
                [
                    'id_ce' => '5',
                    'id_e' => '1',
                    'libelle' => 'Fake GED',
                    'id_connecteur' => 'FakeGED',
                    'type' => 'GED',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => 'Bourg-en-Bresse',
                ],
                [
                    'id_ce' => '6',
                    'id_e' => '1',
                    'libelle' => 'SEDA CG86',
                    'id_connecteur' => 'actes-seda-cg86',
                    'type' => 'Bordereau SEDA',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => 'Bourg-en-Bresse',
                ],
                [
                    'id_ce' => '7',
                    'id_e' => '1',
                    'libelle' => 'SEDA locarchive',
                    'id_connecteur' => 'actes-seda-locarchive',
                    'type' => 'Bordereau SEDA',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => 'Bourg-en-Bresse',
                ],
                [
                    'id_ce' => '8',
                    'id_e' => '1',
                    'libelle' => 'SEDA parametrable',
                    'id_connecteur' => 'actes-seda-parametrable',
                    'type' => 'Bordereau SEDA',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => 'Bourg-en-Bresse',
                ],
                [
                    'id_ce' => '9',
                    'id_e' => '1',
                    'libelle' => 'mail-fournisseur-invitation',
                    'id_connecteur' => 'mail-fournisseur-invitation',
                    'type' => 'mail-fournisseur-invitation',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => 'Bourg-en-Bresse',
                ],
                [
                    'id_ce' => '10',
                    'id_e' => '0',
                    'libelle' => 'Horodateur interne par défaut',
                    'id_connecteur' => 'horodateur-interne',
                    'type' => 'horodateur',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => null,
                ],
                [
                    'id_ce' => '11',
                    'id_e' => '1',
                    'libelle' => 'Mail securise',
                    'id_connecteur' => 'mailsec',
                    'type' => 'mailsec',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => 'Bourg-en-Bresse',
                ],
                [
                    'id_ce' => '12',
                    'id_e' => '1',
                    'libelle' => 'connecteur non associé',
                    'id_connecteur' => 'test',
                    'type' => 'test',
                    'frequence_en_minute' => '1',
                    'id_verrou' => '',
                    'denomination' => 'Bourg-en-Bresse',
                ],
                [
                    'id_ce' => '13',
                    'id_e' => '1',
                    'libelle' => 'Connecteur de test',
                    'id_connecteur' => 'test',
                    'type' => 'test',
                    'frequence_en_minute' => '1',
                    'id_verrou' => 'toto',
                    'denomination' => 'Bourg-en-Bresse',
                ],

            ],
            $this->getInternalAPI()->get('/connecteur/all')
        );
    }

    public function testGetAllWithoutPermission()
    {
        $userId = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class)
            ->create('test', 'test', 'test', 'test@test.test');
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage("Acces interdit id_e=0, droit=connecteur:lecture,id_u=$userId");

        $this->getInternalAPIAsUser($userId)->get('/connecteur/all');
    }

    public function testGetFileFromAnotherEntite()
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class);
        $id_u = $utilisateurSQL->create('badguy', 'foo', 'foo', 'test@bar.baz');

        $roleUtilisateur = $this->getObjectInstancier()->getInstance(RoleUtilisateur::class);
        $roleUtilisateur->addRole($id_u, 'admin', 2);

        $this->getInternalAPI()->post(
            '/entite/1/connecteur/12/file/champs5',
            [
                'file_name' => 'test.txt',
                'file_content' => 'test file content'
            ]
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le connecteur 12 n'appartient pas à l'entité 2");

        $this->getInternalAPIAsUser($id_u)->get('/entite/2/connecteur/12/file/champs5');
    }

    public function testGetExternalData()
    {
        $this->assertSame(
            ["pierre", "feuille", "ciseaux", "lézard", "Spock"],
            $this->getInternalAPI()->get('/entite/1/connecteur/12/externalData/external_data')
        );
    }

    public function testGetExternalDataFromAnotherEntite()
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class);
        $id_u = $utilisateurSQL->create('badguy', 'foo', 'foo', 'test@bar.baz');
        $roleUtilisateur = $this->getObjectInstancier()->getInstance(RoleUtilisateur::class);
        $roleUtilisateur->addRole($id_u, 'admin', 2);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le connecteur 12 n'appartient pas à l'entité 2");
        $this->getInternalAPI()->get('/entite/2/connecteur/12/externalData/external_data');
    }

    public function testPostWithReadPermission()
    {
        $roleSql = $this->getObjectInstancier()->getInstance(RoleSQL::class);
        $roleSql->edit('readonly', 'readonly');
        $roleSql->addDroit('readonly', 'entite:lecture');
        $userId = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class)
            ->create('readonly', 'test', 'test', 'readonly@example.com');
        $this->getObjectInstancier()->getInstance(RoleUtilisateur::class)->addRole($userId, 'readonly', self::ID_E_COL);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Acces interdit id_e=1, droit=connecteur:edition,id_u=3');

        $this->getInternalAPIAsUser($userId)->post('/entite/1/connecteur', [
            'libelle' => 'Connecteur de test',
            'id_connecteur' => 'test'
        ]);
    }

    public function testPostFileWithReadPermission()
    {
        $roleSql = $this->getObjectInstancier()->getInstance(RoleSQL::class);
        $roleSql->edit('readonly', 'readonly');
        $roleSql->addDroit('readonly', 'entite:lecture');
        $userId = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class)
            ->create('readonly', 'test', 'test', 'readonly@example.com');
        $this->getObjectInstancier()->getInstance(RoleUtilisateur::class)->addRole($userId, 'readonly', self::ID_E_COL);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Acces interdit id_e=1, droit=connecteur:edition,id_u=3');

        $this->getInternalAPIAsUser($userId)->post('/entite/1/connecteur/12/file/champs5', [
            'file_name' => 'test.txt',
            'file_content' => 'test file content'
        ]);
    }
}
