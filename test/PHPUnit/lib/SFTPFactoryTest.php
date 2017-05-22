<?php

class SFTPFactoryTest extends PHPUnit_Framework_TestCase {

    public function testGetInsance(){
        $sftpFactory = new SFTPFactory();
        $sftpProperties = new SFTPProperties();
        $this->assertInstanceOf(
            "SFTP",
            $sftpFactory->getInstance($sftpProperties)
        );
    }

}
