<?php

declare(strict_types=1);

namespace Pastell\Tests\Service\Utilisateur;

use ConflictException;
use Pastell\Service\Utilisateur\UserCreationService;
use PastellTestCase;
use UnrecoverableException;
use UtilisateurSQL;

class UserCreationServiceTest extends PastellTestCase
{
    /**
     * @throws UnrecoverableException
     * @throws ConflictException
     */
    public function testCreate(): void
    {
        $userCreationService = $this->getObjectInstancier()->getInstance(UserCreationService::class);

        $userId = $userCreationService->create(
            'login',
            'email@example.org',
            'firstname',
            'lastname',
        );

        $user = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class)->getInfo($userId);

        static::assertSame('login', $user['login']);
    }
}
