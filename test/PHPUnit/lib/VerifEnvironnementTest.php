<?php

class VerifEnvironnementTest extends PHPUnit\Framework\TestCase
{
    public function testCheckExtension()
    {
        $verifEnvironnement = new VerifEnvironnement();

        $this->assertEquals(
            [
                'bcmath' => true,
                'curl' => true,
                'fileinfo' => true,
                'imap' => true,
                'json' => true,
                'ldap' => true,
                'mbstring' => true,
                'openssl' => true,
                'pdo_mysql' => true,
                'redis' => true,
                'soap' => true,
                'zip' => true,
                'Zend OPcache' => true,
                'posix' => true,
                'libxml' => true,
                'xsl' => true,
                'dom' => true,
                'pdo' => true,
                'phar' => true,
                'simplexml' => true,
                'intl' => true,
                'pcntl' => true,
            ],
            $verifEnvironnement->checkExtension()
        );
    }
}
