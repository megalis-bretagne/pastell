<?php

class SFTPProperties
{
    public $host = "localhost";
    public $port = 22;
    public $timeout = 10;

    public $login;
    public $password;

    public $verify_fingerprint = true;
    public $fingerprint;
}
