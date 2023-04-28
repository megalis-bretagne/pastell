<?php

use Pastell\Service\Utilisateur\UserCreationService;
use Pastell\Service\Utilisateur\UserTokenService;

class UtilisateurControlerTest extends ControlerTestCase
{
    /**
     * @return UtilisateurControler
     */
    private function getUtilisateurControler()
    {
        return $this->getControlerInstance(UtilisateurControler::class);
    }

    /**
     * @throws LastErrorException
     */
    public function testDoEditionAction(): void
    {
        $password = 'D@iw3DDf41Nl$DXzMJL!Uc2Yo';
        $this->setPostInfo([
            'login' => 'foo',
            'password' => $password,
            'password2' => $password,
            'nom' => 'baz',
            'prenom' => 'buz',
            'email' => 'boz@byz.fr',
        ]);

        try {
            $this->getUtilisateurControler()->doEditionAction();
        } catch (LastMessageException) {
            /** Nothing to do */
        }

        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        static::assertEquals('boz@byz.fr', $utilisateurSQL->getInfo(3)['email']);
        // Password cannot be set with web controller
        static::assertFalse($utilisateurSQL->verifPassword(3, $password));
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function testModifPasswordAction()
    {
        $this->getObjectInstancier()->setInstance('password_min_entropy', 0);
        $this->getUtilisateurControler()->_beforeAction();
        $this->getUtilisateurControler()->modifPasswordAction();
        $this->expectOutputRegex('#<h1>Modification de votre mot de passe</h1#');
    }

    public function testsuppressionAction(): void
    {
        $this->setGetInfo(['id_u' => 2]);
        $this->getUtilisateurControler()->suppressionAction();
        $this->expectOutputRegex("#<title>Utilisateur Eric Pommateau - Suppression de l(.*)utilisateur  - Pastell</title>#");
    }

    public function testDoSuppressionAction(): void
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        self::assertTrue($utilisateurSQL->exists(2));
        $this->setPostInfo(['id_u' => 2]);
        try {
            $this->getUtilisateurControler()->doSuppressionAction();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        self::assertFalse($utilisateurSQL->exists(2));
        $this->expectOutputRegex("#L'utilisateur 2 a été supprimé#");
    }

    public function testDoSuppressionActionWhenSuicide(): void
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        self::assertTrue($utilisateurSQL->exists(2));
        $this->setPostInfo(['id_u' => 1]);
        try {
            $this->getUtilisateurControler()->doSuppressionAction();
        } catch (Exception $e) {
            self::assertStringContainsString(
                "Impossible de vous supprimer vous-même",
                $e->getMessage()
            );
        }
        self::assertTrue($utilisateurSQL->exists(2));
    }

    public function testDisable(): void
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        self::assertTrue($utilisateurSQL->isEnabled(2));
        $this->setPostInfo(['id_u' => 2]);
        try {
            $this->getUtilisateurControler()->disableAction();
        } catch (Exception $e) {
            /* Nothing to do*/
        }
        self::assertFalse($utilisateurSQL->isEnabled(2));
        self::assertMatchesRegularExpression(
            "#L'utilisateur eric a été désactivé#",
            $this->getLogRecords()[0]['message']
        );
    }

    public function testEnable(): void
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        $utilisateurSQL->disable(2);
        self::assertFalse($utilisateurSQL->isEnabled(2));
        $this->setPostInfo(['id_u' => 2]);
        try {
            $this->getUtilisateurControler()->enableAction();
        } catch (Exception $e) {
            /* Nothing to do*/
        }
        self::assertTrue($utilisateurSQL->isEnabled(2));
        self::assertMatchesRegularExpression(
            "#L'utilisateur eric a été activé#",
            $this->getLogRecords()[0]['message']
        );
    }

    public function testCantDisableMyself(): void
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        self::assertTrue($utilisateurSQL->isEnabled(1));
        $this->setPostInfo(['id_u' => 1]);
        try {
            $this->getUtilisateurControler()->disableAction();
        } catch (Exception $e) {
            self::assertMatchesRegularExpression('#Impossible de vous désactiver vous-même#', $e->getMessage());
            /* Nothing to do*/
        }
        self::assertTrue($utilisateurSQL->isEnabled(1));
    }

    public function testAddToken(): void
    {
        $this->setPostInfo([
            'name' => 'token',
        ]);
        try {
            $this->getUtilisateurControler()->doAddTokenAction();
        } catch (Exception $e) {
            static::assertMatchesRegularExpression('/Votre jeton est <strong>(.*)<\/strong>/', $e->getMessage());
        }
    }

    public function testDeleteToken(): void
    {
        $userTokenService = $this->getObjectInstancier()->getInstance(UserTokenService::class);
        $token = $userTokenService->createToken(self::ID_U_ADMIN, 'token');
        $this->setPostInfo(['id' => 1]);
        try {
            $this->getUtilisateurControler()->deleteTokenAction();
        } catch (Exception $e) {
            self::assertMatchesRegularExpression(
                "/Le jeton a été supprimé/",
                $e->getMessage()
            );
        }
        static::assertNull($userTokenService->getUserFromToken($token));
    }

    public function testRenewToken(): void
    {
        $userTokenService = $this->getObjectInstancier()->getInstance(UserTokenService::class);
        $tokenBefore = $userTokenService->createToken(self::ID_U_ADMIN, 'token');
        $this->setPostInfo(['id' => 1]);
        try {
            $this->getUtilisateurControler()->renewTokenAction();
        } catch (Exception $e) {
            self::assertMatchesRegularExpression(
                "/Le jeton a été renouvelé/",
                $e->getMessage()
            );
        }
        $allTokens = $userTokenService->getTokens(self::ID_U_ADMIN);
        self::assertCount(1, $allTokens);
        self::assertNotContains($tokenBefore, $allTokens);
        self::assertEquals('token', $allTokens[0]['name']);
    }

    public function testDeleteRole(): void
    {
        $this->setPostInfo([
            'id_u' => 2,
            'role' => 'aucun droit',
            'id_e' => 1,
        ]);
        try {
            $this->getUtilisateurControler()->supprimeRoleAction();
        } catch (LastMessageException $e) {
            self::assertMatchesRegularExpression(
                '/Le rôle <i>aucun droit<\/i> a été retiré/',
                $e->getMessage()
            );
        }
    }

    /**
     * @throws UnrecoverableException
     * @throws NotFoundException
     * @throws ConflictException
     * @throws LastMessageException
     */
    public function testAccesPageCreationFail(): void
    {
        $this->setGetInfo([
            'id_e' => 1,
        ]);
        $controller = $this->getUtilisateurControler();
        $user = $this->getObjectInstancier()->getInstance(UserCreationService::class)
            ->create('tester', 'tester@example.org', 'tester', 'tester');
        $this->getObjectInstancier()->getInstance(RoleSQL::class)
            ->edit('entiteLectureEdition', 'Droit utilisateur');
        $this->getObjectInstancier()->getInstance(RoleSQL::class)
            ->addDroit('entiteLectureEdition', 'utilisateur:edition');
        $this->getObjectInstancier()->getInstance(RoleUtilisateur::class)
            ->addRole('3', 'entiteLectureEdition', '1');

        $this->getObjectInstancier()->getInstance(Authentification::class)->connexion('tester', 3);

        $this->expectException(LastErrorException::class);
        $this->expectExceptionMessage("Vous n'avez pas les droits nécessaires (1:utilisateur:creation) pour accéder à cette page");
        $controller->editionAction();
    }

    /**
     * @throws UnrecoverableException
     * @throws NotFoundException
     * @throws LastMessageException
     * @throws LastErrorException
     */
    public function testAccesPageCreation(): void
    {
        \ob_start();
        $this->getUtilisateurControler()->editionAction();
        \ob_get_clean();
        $pageTitle = $this->getUtilisateurControler()->getViewParameterByKey('page_title');
        static::assertSame($pageTitle, 'Nouvel utilisateur ');
    }

    public function testDoEditionActionNoPassword()
    {
        $this->setPostInfo([
            'login' => 'foo',
            'password' => 'D@iw3DDf41Nl$DXzMJL!Uc2Yo',
            'nom' => 'baz',
            'prenom' => 'buz',
            'email' => 'boz@byz.fr'
        ]);
        try {
            $this->getUtilisateurControler()->doEditionAction();
        } catch (LastMessageException $e) {
            /** Nothing to do */
        }
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        $userCreated = $utilisateurSQL->getInfo(3);

        $this->setPostInfo([
            'id_u' => 3,
            'login' => 'foo',
            'nom' => 'baz',
            'prenom' => 'buz',
            'email' => 'boz@byz.fr'
        ]);
        try {
            $this->getUtilisateurControler()->doEditionAction();
        } catch (LastMessageException $e) {
            /** Nothing to do */
        }
        $userUpdated = $utilisateurSQL->getInfo(3);
        self::assertSame($userCreated['password'], $userUpdated['password']);
    }

    public function testNotificationAjoutActionByAdmin(): void
    {
        $this->setPostInfo([
            'id_u' => 2,
            'id_e' => 1,
            'type' => 'actes-generique',
        ]);
        try {
            $this->getUtilisateurControler()->notificationAjoutAction();
        } catch (LastMessageException $e) {
            static::assertStringContainsString(
                'Utilisateur/notification?id_u=2&id_e=1&type=actes-generique:',
                $e->getMessage()
            );
        } catch (LastErrorException | NotFoundException $e) {
            /** Nothing to do */
        }
    }

    /**
     * @throws UnrecoverableException
     * @throws NotFoundException
     * @throws ConflictException
     * @throws LastErrorException
     */
    public function testNotificationAjoutActionBySelf(): void
    {
        $utilisateurControler = $this->getUtilisateurControler();
        $id_u = $this->authenticateNewUserWithPermission(
            ['entite:lecture', 'actes-generique:edition', 'actes-generique:lecture'],
            1
        );
        $this->setPostInfo([
            'id_e' => 1,
            'type' => 'actes-generique',
        ]);
        try {
            $utilisateurControler->notificationAjoutAction();
        } catch (LastMessageException $e) {
            static::assertEquals(3, $id_u);
            static::assertStringContainsString(
                'Utilisateur/notification?id_e=1&type=actes-generique:',
                $e->getMessage()
            );
        }
    }

    public function testNotificationAjoutActionByAdminWithoutType(): void
    {
        $this->setPostInfo([
            'id_u' => 2,
            'id_e' => 1,
            'type' => '',
        ]);
        try {
            $this->getUtilisateurControler()->notificationAjoutAction();
        } catch (LastErrorException $e) {
            static::assertStringContainsString("Vous n'avez selectionné aucun type de dossier", $e->getMessage());
        } catch (LastMessageException | NotFoundException $e) {
            /** Nothing to do */
        }
    }

    public function testNotificationAjoutActionBySelfWithoutType(): void
    {
        $this->setPostInfo([
            'id_e' => 1,
            'type' => '',
        ]);
        try {
            $this->getUtilisateurControler()->notificationAjoutAction();
        } catch (LastErrorException $e) {
            static::assertStringContainsString("Vous n'avez selectionné aucun type de dossier", $e->getMessage());
        } catch (LastMessageException | NotFoundException $e) {
            /** Nothing to do */
        }
    }

    /**
     * @throws LastErrorException
     */
    public function testNotificationByAdminSuppressionActionBySelf(): void
    {
        $utilisateurControler = $this->getUtilisateurControler();
        $utilisateurControler->getNotification()->add(
            2,
            1,
            'actes-generique',
            Notification::ALL_TYPE,
            false
        );
        $this->getObjectInstancier()->getInstance(Authentification::class)->connexion('eric', 2);

        $this->setPostInfo([
            'id_n' => 1,
            'id_e' => 1,
            'type' => 'actes-generique',
        ]);

        try {
            $this->getUtilisateurControler()->notificationSuppressionAction();
        } catch (LastMessageException $e) {
            static::assertStringContainsString(
                'La notification a été supprimée',
                $e->getMessage()
            );
        }
    }

    /**
     * @throws UnrecoverableException
     * @throws NotFoundException
     * @throws LastMessageException
     * @throws LastErrorException
     */
    public function testNotificationModifBySelf(): void
    {
        $utilisateurControler = $this->getUtilisateurControler();
        $utilisateurControler->getNotification()->add(
            2,
            1,
            'actes-generique',
            Notification::ALL_TYPE,
            false
        );
        $this->getObjectInstancier()->getInstance(Authentification::class)->connexion('eric', 2);

        $this->setPostInfo([
            'id_e' => 1,
            'type' => 'actes-generique',
        ]);

        \ob_start();
        $this->getUtilisateurControler()->notificationAction();
        \ob_get_clean();
        static::assertEquals(
            'UtilisateurNotification',
            $utilisateurControler->getViewParameterByKey('template_milieu')
        );
    }

    /**
     * @throws UnrecoverableException
     * @throws NotFoundException
     * @throws LastMessageException
     * @throws LastErrorException
     */
    public function testNotificationModifByAdmin(): void
    {
        $utilisateurControler = $this->getUtilisateurControler();
        $utilisateurControler->getNotification()->add(
            2,
            1,
            'actes-generique',
            Notification::ALL_TYPE,
            false
        );

        $this->setPostInfo([
            'id_u' => 2,
            'id_e' => 1,
            'type' => 'actes-generique',
        ]);

        \ob_start();
        $this->getUtilisateurControler()->notificationAction();
        \ob_get_clean();
        static::assertEquals(
            'UtilisateurNotification',
            $utilisateurControler->getViewParameterByKey('template_milieu')
        );
    }

    /**
     * @throws LastMessageException
     * @throws UnrecoverableException
     * @throws LastErrorException
     * @throws NotFoundException
     */
    public function testDoNotificationModifBySelf(): void
    {
        $utilisateurControler = $this->getUtilisateurControler();
        $utilisateurControler->getNotification()->add(
            2,
            1,
            'actes-generique',
            'creation',
            false
        );
        $this->getObjectInstancier()->getInstance(Authentification::class)->connexion('eric', 2);
        \ob_start();
        $utilisateurControler->moiAction();
        \ob_get_clean();

        $this->setPostInfo([
            'id_e' => 1,
            'type' => 'actes-generique',
            'modification' => [
                'name' => 'modification',
                'type' => 'checkbox',
                'value' => 'on',
                'checked' => true,
            ]
        ]);

        try {
            $this->getUtilisateurControler()->doNotificationEditAction();
        } catch (LastMessageException $e) {
            static::assertStringContainsString(
                'Les notifications ont été modifiées',
                $e->getMessage()
            );
        }

        \ob_start();
        $utilisateurControler->moiAction();
        \ob_get_clean();
        static::assertEquals(
            [
                '1-actes-generique' => [
                'id_n' => 2,
                'id_u' => 1,
                'id_e' => 1,
                'type' => 'actes-generique',
                'action' => [
                  0 => 'En cours de rédaction'
                ],
                'daily_digest' => 0,
                'denomination' => 'Bourg-en-Bresse'
                ]
            ],
            $utilisateurControler->getViewParameterByKey('notification_list')
        );
    }
}
