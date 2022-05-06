<?php

declare(strict_types=1);

namespace Pastell\Helpers;

class UsernameDisplayer
{
    public function getUsername(array $userInfo): string
    {
        if (! empty($userInfo['nom']) || ! empty($userInfo['prenom'])) {
            return $this->linkDecorator($userInfo, sprintf('%s %s', $userInfo['prenom'], $userInfo['nom']));
        }
        if (! empty($userInfo['login'])) {
            return $this->linkDecorator($userInfo, $userInfo['login']);
        }
        if (! isset($userInfo['id_u'])) {
            return 'Utilisateur supprimé';
        }
        if ($userInfo['id_u'] === 0) {
            return 'Action automatique';
        }
        $result = 'Utilisateur supprimé';
        if (! empty($userInfo['id_u'])) {
            $result .= sprintf(' #ID %s', $userInfo['id_u']);
        }
        return $result;
    }

    private function linkDecorator(array $userInfo, string $result): string
    {
        if (empty($userInfo['id_u'])) {
            return $result;
        }
        return sprintf("<a href='/Utilisateur/detail?id_u=%s'>%s</a>", $userInfo['id_u'], get_hecho($result));
    }
}
