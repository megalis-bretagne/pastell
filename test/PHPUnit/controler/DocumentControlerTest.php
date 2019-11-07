<?php

class DocumentControlerTest extends ControlerTestCase
{

    /**
     * @throws Exception
     */
    public function testReindex()
    {

        $info = $this->getInternalAPI()->post("entite/1/document", array('type' => 'test'));

        $this->getInternalAPI()->patch(
            "entite/1/document/{$info['id_d']}",
            array('nom' => 'foo')
        );

        $result = $this->getInternalAPI()->get("entite/1/document?type=test&nom=foo");
        $this->assertEquals($info['id_d'], $result[0]['id_d']);

        $this->getSQLQuery()->query("DELETE FROM document_index");
        $result = $this->getInternalAPI()->get("entite/1/document?type=test&nom=foo");
        $this->assertEmpty($result);

        /** @var DocumentControler $documentController */
        $documentController = $this->getObjectInstancier()->getInstance(DocumentControler::class);
        $this->expectOutputString(
            "Nombre de documents : 1\nRéindexation du document  ({$info['id_d']})\n"
        );
        $documentController->reindex('test', 'nom');
        $result = $this->getInternalAPI()->get("entite/1/document?type=test&nom=foo");
        $this->assertEquals($info['id_d'], $result[0]['id_d']);
    }



    public function testActionActionNoRight()
    {
        $info = $this->getInternalAPI()->post("entite/1/document", array('type' => 'test'));

        $authentification = $this->getObjectInstancier()->getInstance(Authentification::class);
        $authentification->connexion('foo', 42);

        /** @var DocumentControler $documentController */
        $documentController = $this->getObjectInstancier()->getInstance(DocumentControler::class);
        try {
            $this->expectOutputRegex("#id_e=1#");
            $documentController->setGetInfo(new Recuperateur(
                [
                    'id_e' => 1,
                    'id_d' => $info['id_d'],
                    'action' => 'no-way'
                ]
            ));
            $documentController->actionAction();
        } catch (Exception $e) {
        }
        $this->assertEquals(
            "Vous n'avez pas les droits nécessaires (1:test:edition) pour accéder à cette page",
            $documentController->getLastError()->getLastMessage()
        );
    }

    public function testActionAction()
    {
        $info = $this->getInternalAPI()->post("entite/1/document", array('type' => 'test'));

        $authentification = $this->getObjectInstancier()->getInstance(Authentification::class);
        $authentification->connexion('foo', 1);


        /** @var DocumentControler $documentController */
        $documentController = $this->getObjectInstancier()->getInstance(DocumentControler::class);
        try {
            $this->expectOutputRegex("#id_e=1#");
            $documentController->setGetInfo(new Recuperateur(
                [
                    'id_e' => 1,
                    'id_d' => $info['id_d'],
                    'action' => 'no-way'
                ]
            ));
            $documentController->actionAction();
        } catch (Exception $e) {
        }
        $this->assertEquals(
            "L'action no-way a été executée sur le document",
            $documentController->getLastMessage()->getLastMessage()
        );
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function testTextareaReadOnly()
    {
        $info = $this->createDocument('test');

        /** @var DocumentControler $documentControler */
        $documentControler = $this->getControlerInstance(DocumentControler::class);

        $this->setGetInfo(['id_e' => 1,'id_d' => $info['id_d']]);

        $this->setOutputCallback(function ($output) {
            $this->assertEquals(
                0,
                preg_match("#<textarea(.*)name='test_textarea'(.*)</textarea>#s", $output)
            );

            $this->assertEquals(
                1,
                preg_match("#<textarea(.*)name='test_textarea_read_write'(.*)</textarea>#s", $output)
            );
        });
        $documentControler->editionAction();
    }

    public function testListDocument()
    {
        $this->expectOutputRegex('/Liste des dossiers Actes \(générique\) pour Bourg-en-Bresse/');
        $documentController = $this->getControlerInstance(DocumentControler::class);
        $this->setGetInfo([
            'id_e' => 1,
            'type' => 'actes-generique',
            'filtre' => 'modification',
        ]);

        $documentController->listAction();

        $this->assertTrue($documentController->isViewParameter('url'));
        $this->assertSame(
            "id_e=1&search=&type=actes-generique&lastetat=modification",
            $documentController->getViewParameter()['url']
        );
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function testEditOnlyProperties()
    {
        $document_info = $this->createDocument('test');
        /** @var DocumentControler $documentController */
        $documentController = $this->getControlerInstance(DocumentControler::class);
        $this->setGetInfo([
            'id_d' => $document_info['id_d'],
            'id_e' => PastellTestCase::ID_E_COL
        ]);

        ob_start();
        $documentController->editionAction();
        $result = ob_get_contents();
        ob_end_clean();
        $this->assertRegExp("#test_edit_only#", $result);

        ob_start();
        $documentController->detailAction();
        $result = ob_get_contents();
        ob_end_clean();
        $this->assertNotRegExp("#test_edit_only#", $result);
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function testIndexActionAsNotSuperadmin()
    {
        $authentification = $this->getObjectInstancier()->getInstance(Authentification::class);
        $authentification->connexion('eric', 2);

        /** @var DocumentControler $documentController */
        $documentController = $this->getObjectInstancier()->getInstance(DocumentControler::class);

        ob_start();
        $documentController->indexAction();
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertNotRegExp('/Veuillez sélectionner une entité afin de pouvoir visualiser ses dossiers/', $result);
    }

    public function testIndexWithoutRight()
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class);
        $id_u = $utilisateurSQL->create("badguy", "foo", "foo", "test@bar.baz");

        $roleUtilisateur = $this->getObjectInstancier()->getInstance(RoleUtilisateur::class);
        $roleUtilisateur->addRole($id_u, "admin", 2);
        $this->getObjectInstancier()->Authentification->Connexion('admin', $id_u);

        $documentController = $this->getObjectInstancier()->getInstance(DocumentControler::class);
        $documentController->setGetInfo(new Recuperateur(['id_e' => 1,]));
        try {
            ob_start(); //Very uggly...
            $documentController->indexAction();
            $this->assertTrue(false);
        } catch (Exception $e) {
            /* Nothing to do */
        }
        ob_end_clean();
        $this->assertEquals(
            "Vous n'avez pas les droits nécessaires pour accéder à cette page",
            $documentController->getLastError()->getLastError()
        );
    }

    public function testIndexWithTwoRoleOnTwoEntities()
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class);
        $id_u = $utilisateurSQL->create("badguy", "foo", "foo", "test@bar.baz");

        $roleUtilisateur = $this->getObjectInstancier()->getInstance(RoleUtilisateur::class);
        $roleUtilisateur->addRole($id_u, "admin", 2);
        $roleUtilisateur->addRole($id_u, "admin", 1);
        $this->getObjectInstancier()->Authentification->Connexion('admin', $id_u);

        $documentController = $this->getObjectInstancier()->getInstance(DocumentControler::class);

        ob_start();
        $documentController->indexAction();
        $result = ob_get_contents();
        ob_end_clean();
        $this->assertContains("Bourg-en-Bresse", $result);
        $this->assertContains("CCAS", $result);
    }

    public function testIndexWithTwoDifferentRoleOnTwoEntities()
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class);
        $id_u = $utilisateurSQL->create("badguy", "foo", "foo", "test@bar.baz");

        $roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);
        $roleSQL->addDroit('utilisateur', 'actes-generique:lecture');

        $roleUtilisateur = $this->getObjectInstancier()->getInstance(RoleUtilisateur::class);
        $roleUtilisateur->addRole($id_u, "admin", 2);
        $roleUtilisateur->addRole($id_u, "utilisateur", 1);
        $this->getObjectInstancier()->Authentification->Connexion('admin', $id_u);

        $documentController = $this->getObjectInstancier()->getInstance(DocumentControler::class);
        $documentController->setGetInfo(new Recuperateur(['id_e' => 1]));

        ob_start();
        $documentController->indexAction();
        $result = ob_get_contents();
        ob_end_clean();
        $this->assertContains("Bourg-en-Bresse", $result);
        $this->assertContains("CCAS", $result);
    }


    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testDoEditionCallsOnChangeWhenUploadingAFile()
    {
        $document = $this->createDocument('helios-generique');
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->addFileFromCopy(
            'fichier_pes',
            'PES.xml',
            __DIR__ . '/../pastell-core/fixtures/HELIOS_SIMU_ALR2_1496987735_826268894.xml'
        );
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $this->assertFalse($donneesFormulaire->get('objet'));

        $this->getObjectInstancier()->getInstance(Authentification::class)->connexion('admin', self::ID_U_ADMIN);

        $documentController = $this->getObjectInstancier()->getInstance(DocumentControler::class);
        $documentController->setPostInfo(new Recuperateur([
            'id_e' => self::ID_E_COL,
            'id_d' => $document['id_d'],
            'fieldSubmittedId' => 'fichier_pes'
        ]));

        try {
            ob_start();
            $documentController->doEditionAction();
        } catch (Exception $e) {
            /* Nothing to do */
        }
        ob_end_clean();
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $this->assertSame(
            'HELIOS_SIMU_ALR2_1496987735_826268894.xml',
            $donneesFormulaire->get('objet')
        );
    }
}
