<?php

class VerifEnvironnementTest extends PHPUnit\Framework\TestCase
{
    public function testCheckExtension()
    {
        $verifEnvironnement = new VerifEnvironnement();

        $this->assertEquals(
            array (
                'bcmath' => true,
                'curl' => true,
                'fileinfo' => true,
                'imap' => true,
                'json' => true,
                'ldap' => true,
                'mbstring' => true,
                'openssl' => true,
                'PDO' => true,
                'pdo_mysql' => true,
                'Phar' => true,
                'redis' => true,
                'SimpleXML' => true,
                'soap' => true,
                'ssh2' => true,
                'zip' => true,
                'Zend OPcache' => true,
                'posix' => true,
                'libxml' => true,
                'xsl' => true,
                'dom' => true,
            ),
            $verifEnvironnement->checkExtension()
        );
    }
}
