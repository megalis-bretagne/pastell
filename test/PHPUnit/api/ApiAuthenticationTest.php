<?php

use Pastell\Service\LoginAttemptLimit;

class ApiAuthenticationTest extends PastellTestCase
{
    public function testOk(): void
    {
        $loginAttemptLimit = $this->createMock(LoginAttemptLimit::class);
        $loginAttemptLimit->method('isLoginAttemptAuthorized')->willReturn(true);
        $this->getObjectInstancier()->setInstance(LoginAttemptLimit::class, $loginAttemptLimit);
        $httpApi = $this->getObjectInstancier()->getInstance(HttpApi::class);
        $httpApi->setServerArray(['REQUEST_METHOD' => 'get', 'PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'admin']);
        $httpApi->setGetArray(['api_function' => 'v2/version']);
        $this->expectOutputRegex('#1.4-fixtures#');
        $httpApi->dispatch();
    }

    public function testWithADisabledAccount(): void
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        $utilisateurSQL->disable(1);
        $loginAttemptLimit = $this->createMock(LoginAttemptLimit::class);
        $loginAttemptLimit->method('isLoginAttemptAuthorized')->willReturn(true);
        $this->getObjectInstancier()->setInstance(LoginAttemptLimit::class, $loginAttemptLimit);
        $httpApi = $this->getObjectInstancier()->getInstance(HttpApi::class);
        $httpApi->setServerArray(['REQUEST_METHOD' => 'get', 'PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'admin']);
        $httpApi->setGetArray(['api_function' => 'v2/version']);
        $this->expectOutputRegex("#Votre compte a#");
        $httpApi->dispatch();
    }
}
