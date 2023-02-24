<?php

declare(strict_types=1);

class SFTPProperties
{
    public string $host = 'localhost';
    public int $port = 22;
    public int $timeout = 10;

    public string $login;
    public string $password;

    public bool $verifyFingerprint = true;
    public string $fingerprint;
}
