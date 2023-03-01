<?php

class LDAPTestRecupEntry extends ActionExecutor
{
    public function convert_multi_array($array)
    {
        ob_start();
        print_r($array);
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }

    public function go()
    {
        /** @var LDAPVerification $ldap */
        $ldap = $this->getMyConnecteur();
        $login = $this->objectInstancier->getInstance(Authentification::class)->getLogin();
        $entry = $ldap->getEntry($login);
        if (!$entry) {
            throw new Exception("L'entrée $login n'a pas été trouvé");
        }
        $this->setLastMessage("Mon entrée sur l'annuaire LDAP : <pre>" . $this->convert_multi_array($entry) . "</pre>");
        return true;
    }
}