<?php

abstract class AuthenticationConnecteur extends Connecteur
{
    abstract public function authenticate($redirectUrl = false);

    abstract public function logout($redirectUrl = false);

    abstract public function getExternalSystemName(): string;

    abstract public function getRedirectUrl(): string;
}
