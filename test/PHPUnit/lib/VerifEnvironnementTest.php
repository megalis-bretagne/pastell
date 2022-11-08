<?php

declare(strict_types=1);

class VerifEnvironnementTest extends PastellTestCase
{
    public function testCheckExtension(): void
    {
        $verifEnvironnement = $this->getObjectInstancier()->getInstance(VerifEnvironnement::class);
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
                'uuid' => true,
            ],
            $verifEnvironnement->checkExtension()
        );
    }
}
