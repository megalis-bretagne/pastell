<?php

namespace Pastell\Tests\Service\Connecteur;

use Exception;
use Pastell\Service\Connecteur\MissingConnecteurService;
use PastellTestCase;
use TmpFolder;
use ZipArchive;

class MissingConnecteurServiceTest extends PastellTestCase
{
    private function getMissingConnecteurService()
    {
        return $this->getObjectInstancier()->getInstance(MissingConnecteurService::class);
    }

    public function testAll()
    {
        $all = $this->getMissingConnecteurService()->listAll();
        $this->assertEquals('SEDA CG86', $all['actes-seda-cg86'][0]['libelle']);
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
        $tmpFolder->delete($tmp_folder);
    }
}
