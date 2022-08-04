<?php

declare(strict_types=1);

final class UsersToken extends SQL
{
    public function create(int $userId, string $name, string $token, ?string $expirationDate = null): void
    {
        $now = \date(Date::DATE_ISO);

        $query = <<<EOT
INSERT INTO users_token(id_u, name, token, created_at, expired_at)
VALUES(?,?,?,?,?);
EOT;

        $this->query($query, $userId, $name, $token, $now, $expirationDate);
    }

    public function getTokensOfUser(int $userId): array
    {
        $query = <<<EOT
SELECT id, id_u, name, created_at, expired_at
FROM users_token
WHERE id_u=?;
EOT;

        return $this->query($query, $userId);
    }

    public function getUser(int $tokenId): ?array
    {
        $query = <<<EOT
SELECT user.id_u, user.login
FROM users_token
JOIN utilisateur user on users_token.id_u = user.id_u
WHERE id=?;
EOT;

        $output = $this->queryOne($query, $tokenId);
        if ($output === false) {
            return null;
        }
        return $output;
    }

    public function getUserFromToken(string $token): ?array
    {
        $query = <<<EOT
SELECT user.id_u, user.login, expired_at
FROM users_token
JOIN utilisateur user on users_token.id_u = user.id_u
WHERE token=?;
EOT;

        $output = $this->queryOne($query, $token);
        if ($output === false) {
            return null;
        }
        return $output;
    }


    public function deleteToken(int $tokenId): void
    {
        $query = 'DELETE FROM users_token WHERE id=?;';
        $this->query($query, $tokenId);
    }
}
