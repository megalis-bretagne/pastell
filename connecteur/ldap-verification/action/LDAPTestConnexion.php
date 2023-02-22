<?php

class LDAPTestConnexion extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var LDAPVerification $ldap */
        $ldap = $this->getMyConnecteur();
        $ldap->getConnexion();
        $this->setLastMessage("La connexion est ok");
        return true;
    }
}
