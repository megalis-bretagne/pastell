<?php

class SFTPFactoryTest extends PHPUnit\Framework\TestCase
{
    public function testGetInsance()
    {
        $sftpFactory = new SFTPFactory();
        $sftpProperties = new SFTPProperties();
        $this->assertInstanceOf(
            "SFTP",
            $sftpFactory->getInstance($sftpProperties)
        );
    }
}
