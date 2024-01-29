<?php

namespace Pastell\Tests\Service\Connecteur;

use Exception;
use Pastell\Service\Connecteur\MissingConnecteurService;
use PastellTestCase;
use TmpFolder;
use ZipArchive;

class MissingConnecteurServiceTest extends PastellTestCase
{
    protected function tearDown(): void
    {
        $this->setListPack(["suppl_test" => true]);
    }

    private function getMissingConnecteurService()
    {
        return $this->getObjectInstancier()->getInstance(MissingConnecteurService::class);
    }

    /**
     * @throws Exception
     */
    public function testExportAll()
    {
        $this->getObjectInstancier()->setInstance("workspacePath", "/tmp/");

        $donneesFormulaire = $this->getConnecteurFactory()->getConnecteurConfig(6);
        $donneesFormulaire->addFileFromData("fake_file", "foo.txt", "bar");

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        $tmp_file = $tmp_folder . "/test.zip";
        $this->getMissingConnecteurService()->exportAll($tmp_file);

        $zipArchive = new ZipArchive();
        $zipArchive->open($tmp_file);
        $zipArchive->extractTo($tmp_folder);
        $tmp_folder_content = (scandir($tmp_folder));

        $this->assertContains('connecteur_3.json', $tmp_folder_content);
        $this->assertContains("connecteur_6.yml_fake_file_0", $tmp_folder_content);
        $this->assertNotContains("connecteur_12.json", $tmp_folder_content);
        $this->assertNotContains("connecteur_13.json", $tmp_folder_content);

        $tmpFolder->delete($tmp_folder);
    }

    /**
     * @throws Exception
     */
    public function testExportAllWithRestricted()
    {
        $this->setListPack(["suppl_test" => false]);

        $this->getObjectInstancier()->setInstance("workspacePath", "/tmp/");

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        $tmp_file = $tmp_folder . "/test.zip";
        $this->getMissingConnecteurService()->exportAll($tmp_file);

        $zipArchive = new ZipArchive();
        $zipArchive->open($tmp_file);
        $zipArchive->extractTo($tmp_folder);
        $tmp_folder_content = (scandir($tmp_folder));

        $this->assertContains('connecteur_3.json', $tmp_folder_content);
        $this->assertContains("connecteur_12.json", $tmp_folder_content);
        $this->assertContains("connecteur_13.json", $tmp_folder_content);

        $tmpFolder->delete($tmp_folder);
        $this->setListPack(["suppl_test" => true]);
    }
}
