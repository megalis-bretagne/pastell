<?php

class LDAPWrapper
{
    /**
     * @param resource $link_identifier
     * @param string|null $bind_rdn
     * @param string|null $bind_password
     * @return bool
     */
    public function ldap_bind($link_identifier, ?string $bind_rdn = null, ?string $bind_password = null): bool
    {
        return ldap_bind($link_identifier, $bind_rdn, $bind_password);
    }

    /**
     * @param string|null $hostname
     * @param int $port
     * @return false|resource
     */
    public function ldap_connect(?string $hostname = null, int $port = 389)
    {
        return ldap_connect($hostname, $port);
    }

    /**
     * @param resource $link_identifier
     * @return ?string
     */
    public function ldap_error($link_identifier): ?string
    {
        return ldap_error($link_identifier) ;
    }

    /**
     * @param resource $ldap_identifier
     * @param int $option
     * @param mixed $newval
     * @return bool
     */
    public function ldap_set_option($ldap_identifier, int $option, $newval): bool
    {
        return ldap_set_option($ldap_identifier, $option, $newval);
    }

    /**
     * @param resource $link_identifier
     * @param string $base_dn
     * @param string $filter
     * @param array $attributes
     * @param int|null $attrsonly
     * @param int|null $sizelimit
     * @param int|null $timelimit
     * @param int|null $deref
     * @return false|resource
     */
    public function ldap_search(//NOSONAR
        $link_identifier,
        string $base_dn,
        string $filter,
        array $attributes = [],
        int $attrsonly = null,
        int $sizelimit = null,
        int $timelimit = null,
        int $deref = null
    ) { //NOSONAR
        return ldap_search(
            $link_identifier,
            $base_dn,
            $filter,
            $attributes,
            $attrsonly,
            $sizelimit,
            $timelimit,
            $deref
        );
    }

    /**
     * @param resource $link_identifier
     * @param resource $ressource
     * @return false|int
     */
    public function ldap_count_entries($link_identifier, $ressource)
    {
        return ldap_count_entries($link_identifier, $ressource);
    }

    /**
     * @param resource $link_identifier
     * @param resource $result_identifier
     * @return array
     */
    public function ldap_get_entries($link_identifier, $result_identifier)
    {
        return ldap_get_entries($link_identifier, $result_identifier);
    }
}
