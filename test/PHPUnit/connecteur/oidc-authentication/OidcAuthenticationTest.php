<?php

use Jumbojett\OpenIDConnectClient;
use Pastell\Security\Authentication\OpenIDConnectClientFactory;

class OidcAuthenticationTest extends PastellTestCase
{
    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testAuthenticate(): void
    {
        $response = (object) [
            'preferred_username' => "foo",
            'given_name' => 'bar',
            'family_name' => 'baz',
            'email' => 'foo@bar'
        ];

        $openIDConnectClient = $this->createMock(OpenIDConnectClient::class);
        $openIDConnectClient->expects($this->any())
            ->method('requestUserInfo')
            ->willReturn($response);

        $openIDConnectClientFactory = $this->createMock(OpenIDConnectClientFactory::class);
        $openIDConnectClientFactory->expects($this->any())
            ->method('getInstance')
            ->willReturn($openIDConnectClient);

        $oidcAuthentication = $this->getObjectInstancier()->getInstance(OidcAuthentication::class);
        $oidcAuthentication->setOpenIDConnectClientFactory($openIDConnectClientFactory);

        $id_ce = $this->createConnector('oidc-authentication', "OIDC", 0)['id_ce'];
        $this->configureConnector($id_ce, [
            'login_attribute' => 'preferred_username',
            'user_creation' => 'On',
            'given_name_attribute' => 'given_name',
            'family_name_attribute' => 'family_name',
            'email_attribute' => 'email',
            'redirect_url' => 'toto',
        ], 0);
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);

        $oidcAuthentication->setConnecteurConfig($donneesFormulaire);
        self::assertEquals('foo', $oidcAuthentication->authenticate());
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        $id_u = $utilisateurSQL->getIdFromLogin('foo');
        $userInfo = $utilisateurSQL->getInfo($id_u);
        self::assertEquals('foo', $userInfo['login']);
        self::assertEquals('baz', $userInfo['nom']);
        self::assertEquals('bar', $userInfo['prenom']);
        self::assertEquals('foo@bar', $userInfo['email']);
    }
}
