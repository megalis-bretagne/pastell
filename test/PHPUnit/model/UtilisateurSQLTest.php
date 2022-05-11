<?php

declare(strict_types=1);

class UtilisateurSQLTest extends PastellTestCase
{
    public function testEnable(): void
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        $utilisateurSQL->enable(1);
        self::assertTrue($utilisateurSQL->isEnable(1));
    }

    public function testDisable(): void
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        $utilisateurSQL->disable(1);
        self::assertFalse($utilisateurSQL->isEnable(1));
    }
}
