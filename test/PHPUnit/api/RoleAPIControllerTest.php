<?php

class RoleAPIControllerTest extends PastellTestCase
{
    public function testList(): void
    {
        $list = $this->getInternalAPI()->get('/role');
        static::assertSame(
            [
                [
                    'role' => 'admin',
                    'libelle' => 'Administrateur',
                ],
                [
                    'role' => 'autre',
                    'libelle' => 'autre rôle',
                ],
                [
                    'role' => 'utilisateur',
                    'libelle' => 'utilisateur sans rôle',
                ],
            ],
            $list
        );
    }

    public function testListFailed(): void
    {
        $internalAPI = $this->getInternalAPI();
        $internalAPI->setUtilisateurId(42);
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Vous devez avoir le droit role:lecture pour accéder à la ressource.');
        $internalAPI->get('/role');
    }

    public function testV1()
    {
        $this->expectOutputRegex("#Administrateur#");
        $this->getV1("list-roles.php");
    }
}
