<?php

class LDAPWrapper
{

    public function ldap_bind($link_identifier, string $bind_rdn = null, string $bind_password = null): bool
    {
        return ldap_bind($link_identifier, $bind_rdn, $bind_password);
    }

    public function ldap_connect(string $hostname = null, int $port = 389)
    {
        return ldap_connect($hostname, $port);
    }

    public function ldap_error($link_identifier): string
    {
        return ldap_error($link_identifier);
    }

    public function ldap_set_option($ldap_identifier, $option, $newval): bool
    {
        return ldap_set_option($ldap_identifier, $option, $newval);
    }

    public function ldap_search(//NOSONAR
        $link_identifier,
        $base_dn,
        $filter,
        array $attributes = [],
        $attrsonly = null,
        $sizelimit = null,
        $timelimit = null,
        $deref = null
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

    public function ldap_count_entries($link_identifier, $ressource)
    {
        return ldap_count_entries($link_identifier, $ressource);
    }

    public function ldap_get_entries($link_identifier, $result_identifier)
    {
        return ldap_get_entries($link_identifier, $result_identifier);
    }
}
