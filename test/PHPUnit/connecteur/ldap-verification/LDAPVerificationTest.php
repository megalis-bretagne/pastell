<?php

class LDAPVerificationTest extends PastellTestCase
{
    public function getLDAPFilter()
    {
        yield 'test with parenthesis' => ['(memberOf=pastell)'];
        yield 'test without parenthesis' => ['memberOf=pastell'];
    }

    /**
     * @throws UnrecoverableException
     * @dataProvider getLDAPFilter
     * @param string $ldap_filter
     */
    public function testGetEntry(string $ldap_filter)
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

    private function setLDAPWrapper(): void
    {
        $ldapWrapper = $this->getMockBuilder(LDAPWrapper::class)->getMock();
        $ldapWrapper
            ->method('ldap_search')
            ->willReturnCallback(function ($link, $base_dn, $filter) {
                static::assertTrue($link);
                static::assertSame('dc=exemple,dc=com', $base_dn);
                static::assertSame('(&(memberOf=pastell)(sAMAccountName=my_user))', $filter);
                return ['test'];
            });

        $ldapWrapper
            ->method('ldap_count_entries')
            ->willReturn(1);

        $ldapWrapper
            ->method('ldap_get_entries')
            ->willReturn([0 => ['dn' => 'foo']]);

        $ldapWrapper
            ->method('ldap_connect')
            ->willReturn(true);

        $ldapWrapper
            ->method('ldap_bind')
            ->willReturn(true);

        $this->getObjectInstancier()->setInstance(LDAPWrapper::class, $ldapWrapper);
    }

    public function testWithoutPort()
    {
        $id_ce = $this->createConnector('ldap-verification', 'LDAP', 0)['id_ce'];
        /** @var LDAPVerification $ldapVerification */
        $ldapVerification = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage("Impossible de s'authentifier sur le serveur LDAP : Can't contact LDAP server");
        $ldapVerification->getConnexion();
    }

    /**
     * @throws UnrecoverableException
     * @dataProvider getLDAPFilter
     * @param string $ldap_filter
     */
    public function testGetEntryMissingUser(string $ldap_filter): void
    {
        $this->setLDAPWrapperMissingUser();
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
        $this->assertFalse(
            $ldapVerification->getEntry("my_user")
        );
    }

    private function setLDAPWrapperMissingUser(): void
    {
        $ldapWrapper = $this->getMockBuilder(LDAPWrapper::class)->getMock();
        $ldapWrapper
            ->method('ldap_search')
            ->willReturnCallback(function ($link, $base_dn, $filter) {
                static::assertTrue($link);
                static::assertSame('dc=exemple,dc=com', $base_dn);
                static::assertSame('(&(memberOf=pastell)(sAMAccountName=my_user))', $filter);
                return ['test'];
            });

        $ldapWrapper
            ->method('ldap_count_entries')
            ->willReturn(0);

        $ldapWrapper
            ->method('ldap_connect')
            ->willReturn(true);

        $ldapWrapper
            ->method('ldap_bind')
            ->willReturn(true);

        $this->getObjectInstancier()->setInstance(LDAPWrapper::class, $ldapWrapper);
    }
}
