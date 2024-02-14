<?php

namespace Pastell\Connector\Ensap;

class GPGEncryptor
{
    public function encrypt(string $archiveName, $archivePath): string
    {
        return $archivePath . '/' . $archiveName . '.gpg';
    }
}
