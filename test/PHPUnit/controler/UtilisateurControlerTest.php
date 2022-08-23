<?php

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
    public function testDoEditionAction()
    {
        $this->setPostInfo([
            'login' => 'foo',
            'password' => 'bar',
            'password2' => 'bar',
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
        $this->assertEquals('boz@byz.fr', $utilisateurSQL->getInfo(3)['email']);
        $this->assertTrue($utilisateurSQL->verifPassword(3, "bar"));
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function testWhenPassword2IsNotSet()
    {
        $this->setPostInfo([
            'login' => 'foo',
            'password' => 'bar',
            'password2' => '',
            'nom' => 'baz',
            'prenom' => 'buz',
            'email' => 'boz@byz.fr'
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Les mots de passe ne correspondent pas");
        $this->getUtilisateurControler()->doEditionAction();
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
        $this->expectOutputRegex("#<title>Utilisateur Eric Pommateau - Suppression de l'utilisateur  - Pastell</title>#");
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
}
