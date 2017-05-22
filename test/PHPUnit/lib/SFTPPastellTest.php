<?php

class SFTPPastellTest extends PHPUnit_Framework_TestCase {

    /** @var  SFTP */
    private $sftp;

    /** @var  SFTPProperties */
    private $sftpProperties;

    protected function setUp() {
        parent::setUp();
        $this->sftpProperties = new SFTPProperties();
        $this->sftpProperties->host = "localhost";
        $this->sftpProperties->login = "admin";
        $this->sftpProperties->password = "password";
        $this->sftpProperties->fingerprint = "DA39A3EE5E6B4B0D3255BFEF95601890AFD80709";
        $this->setSFTP();
    }

    private function setSFTP(){
        $netSFTP = $this->getMockBuilder("\phpseclib\Net\SFTP")
            ->disableOriginalConstructor()
            ->getMock();

        $closure = function($a){
            if ($a == 'foo bar'){
                throw new Exception("NET_SFTP_STATUS_NO_SUCH_FILE: No such file");
            }
            return   array('.','..','foo');
        };

        $netSFTP
            ->expects($this->any())
            ->method('nlist')
            //->willReturn(array('.','..','foo'));
            ->will($this->returnCallback($closure));
        if ($this->sftpProperties->host == 'foo'){
            $netSFTP
                ->expects($this->any())
                ->method("login")
                ->willThrowException(new Exception("Cannot connect to foo:22"));
        }

        /** @var \phpseclib\Net\SFTP $netSFTP */
        $this->sftp = new SFTP($netSFTP,$this->sftpProperties);
    }


    public function testListDirectory(){
        $result = $this->sftp->listDirectory("/tmp/");
        $this->assertTrue(in_array('foo',$result));
    }

    public function testBadHost(){
        $this->sftpProperties->host = "foo";
        $this->setSFTP();
        $this->setExpectedExceptionRegExp("Exception","#Cannot connect to foo:22#");
        $this->sftp->listDirectory("/tmp/");
    }

    public function testBadDirectory(){
        $this->setExpectedException("Exception","NET_SFTP_STATUS_NO_SUCH_FILE: No such file");
        $this->sftp->listDirectory("foo bar");
    }

    public function testBadFingerPrint(){
        $this->sftpProperties->fingerprint = "foo";
        $this->setSFTP();
        $this->setExpectedException("Exception","L'empreinte du serveur (DA39A3EE5E6B4B0D3255BFEF95601890AFD80709) ne correspond pas");
        $this->sftp->listDirectory("/tmp/");
    }

    public function testRetrieveFile(){
        $this->assertTrue(
            $this->sftp->get("/Users/eric/test1","/var/tmp/toto")
        );
    }

    public function testPut(){
        $this->assertTrue($this->sftp->put("/tmp/test42","/tmp/put.txt"));
    }
    public function testDelete(){
        $this->assertTrue($this->sftp->delete("/tmp/test42"));
    }

    public function testMkdir(){
        $this->assertTrue(
            $this->sftp->mkdir("/tmp/bar")
        );
    }

}

