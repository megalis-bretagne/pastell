<?php

declare(strict_types=1);

namespace Pastell\Tests\Service\Utilisateur;

use ConflictException;
use Pastell\Service\Utilisateur\UserUpdateService;
use PastellTestCase;
use UnrecoverableException;
use UtilisateurSQL;

class UserUpdateServiceTest extends PastellTestCase
{
    /**
     * @throws UnrecoverableException
     * @throws ConflictException
     */
    public function testUpdate(): void
    {
        $userUpdateService = $this->getObjectInstancier()->getInstance(UserUpdateService::class);

        $userId = $userUpdateService->update(
            1,
            'admin',
            'email@example.org',
            'firstname',
            'lastname',
        );

        $user = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class)->getInfo($userId);

        static::assertSame('admin', $user['login']);
        static::assertSame('email@example.org', $user['email']);
    }
}
