<?php

declare(strict_types=1);

namespace Pastell\Tests\Bootstrap;


use Pastell\Bootstrap\CreateCertificate;
use Pastell\Bootstrap\InstallResult;
use PastellTestCase;

class CreateCertificateTest extends PastellTestCase
{
    public function testInstall()
    {
        $tmpFolder = $this->getObjectInstancier()->getInstance(\TmpFolder::class);
        $tmp_folder = $tmpFolder->create();
        $this->getObjectInstancier()->setInstance('certificate_path', $tmp_folder);
        $createCertificate = $this->getObjectInstancier()->getInstance(CreateCertificate::class);
        $this->assertEquals(InstallResult::InstallOk, $createCertificate->install());
        $this->assertFileExists($tmp_folder."/privkey.pem");
    }
}