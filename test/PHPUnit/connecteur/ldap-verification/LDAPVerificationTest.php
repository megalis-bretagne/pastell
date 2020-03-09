<?php

class LDAPVerificationTest extends PastellTestCase
{

    /**
     * @throws Exception
     */
    public function testGetEntryWhenFilterHasParenthesis()
    {
        $this->callGetEntryWithFilter('(memberOf=pastell)');
    }

    /**
     * @throws Exception
     */
    public function testGetEntryWhenFilterHasntParenthesis()
    {
        $this->callGetEntryWithFilter('memberOf=pastell');
    }

    private function setLDAPWrapper()
    {
        $ldapWrapper = $this->getMockBuilder(LDAPWrapper::class)->getMock();
        $ldapWrapper->expects($this->any())
            ->method('ldap_search')
            ->willReturnCallback(function ($link, $base_dn, $filter) {
                $this->assertTrue($link);
                $this->assertEquals('dc=exemple,dc=com', $base_dn);
                $this->assertEquals("(&(memberOf=pastell)(sAMAccountName=my_user))", $filter);
                return true;
            });

        $ldapWrapper->expects($this->any())
            ->method('ldap_count_entries')
            ->willReturn(1);

        $ldapWrapper->expects($this->any())
            ->method('ldap_get_entries')
            ->willReturn([0 => ['dn' => 'foo']]);

        $ldapWrapper->expects($this->any())
            ->method('ldap_connect')
            ->willReturn(true);

        $ldapWrapper->expects($this->any())
            ->method('ldap_bind')
            ->willReturn(true);

        $this->getObjectInstancier()->setInstance(LDAPWrapper::class, $ldapWrapper);
    }

    /**
     * @param $ldap_filter
     * @throws Exception
     */
    private function callGetEntryWithFilter($ldap_filter)
    {
        $this->setLDAPWrapper();
        $id_ce = $this->createConnector('ldap-verification', 'LDAP', 0)['id_ce'];

        $this->configureConnector(
            $id_ce,
            [
                'ldap_host' => 'test.pastell',
                'ldap_port' => 689,
                'ldap_user' => "foo",
                'ldap_password' => 'bar',
                'ldap_root' => 'dc=exemple,dc=com',
                'ldap_login_attribute' => 'sAMAccountName',
                'ldap_filter' => $ldap_filter
            ],
            0
        );

        /** @var LDAPVerification $ldapVerification */
        $ldapVerification = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $this->assertEquals(
            'foo',
            $ldapVerification->getEntry("my_user")
        );
    }
}
