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
        $now = \time();
        $tokens = $this->usersToken->getTokensOfUser($userId);
        \array_walk($tokens, static function (&$token) use ($now) {
            if ($token['expired_at'] === null) {
                $isExpired = false;
            } else {
                $isExpired = \strtotime($token['expired_at']) < $now;
            }
            $token['is_expired'] = $isExpired;
        });
        return $tokens;
    }

    public function getUser(int $tokenId): ?int
    {
        return $this->usersToken->getUser($tokenId)['id_u'] ?? null;
    }

    public function getUserFromToken(string $token): ?array
    {
        $user = $this->usersToken->getUserFromToken($token);
        if ($user !== null) {
            if ($user['expired_at'] === null) {
                $isExpired = false;
            } else {
                $isExpired = \strtotime($user['expired_at']) < \time();
            }
            $user['is_expired'] = $isExpired;
        }
        return $user;
    }

    public function deleteToken(int $tokenId): void
    {
        $this->usersToken->deleteToken($tokenId);
    }
}
