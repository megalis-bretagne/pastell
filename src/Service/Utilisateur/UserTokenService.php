<?php

declare(strict_types=1);

namespace Pastell\Service\Utilisateur;

use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use UsersToken;

final class UserTokenService
{
    public function __construct(
        private readonly UsersToken $usersToken,
    ) {
    }

    public function createToken(int $userId, string $name, string $expiration = null): string
    {
        $token = (new UriSafeTokenGenerator())->generateToken();
        $this->usersToken->create($userId, $name, $token, $expiration);
        return $token;
    }

    public function getTokens(int $userId): array
    {
        return $this->usersToken->getTokensOfUser($userId);
    }

    public function getUser(int $tokenId): ?int
    {
        return $this->usersToken->getUser($tokenId)['id_u'] ?? null;
    }

    public function getUserFromToken(string $token): ?array
    {
        return $this->usersToken->getUserFromToken($token);
    }

    public function deleteToken(int $tokenId): void
    {
        $this->usersToken->deleteToken($tokenId);
    }
}
