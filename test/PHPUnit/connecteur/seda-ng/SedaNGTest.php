<?php

class SedaNGTest extends PastellTestCase {

	/**
	 * @throws Exception
	 */
	public function testGenerateArchive(){
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        $archive_path = $tmp_folder."/archive.tar.gz";

        $fluxData = $this->getMockBuilder("FluxData")->disableOriginalConstructor()->getMock();

        $fluxData->expects($this->any())->method('getFilelist')->willReturn([[
            'key' => 'fichier',
            'filename' => 'connecteur_exemple.yml',
            'filepath' => __DIR__.'/fixtures/connecteur_exemple.yml',
        ]]);

        /** @var FluxData $fluxData */

        $sedaNG = new SedaNG();
        $sedaNG->generateArchive($fluxData,$archive_path);

        exec("tar xvzf $archive_path -C $tmp_folder");
        $tmp_content = scandir($tmp_folder);
        $this->assertEquals('connecteur_exemple.yml',$tmp_content[3]);
        $this->assertFileEquals(
            __DIR__.'/fixtures/connecteur_exemple.yml',
            $tmp_folder."/$tmp_content[3]"
        );
    }

	/**
	 * @throws Exception
	 */
	public function testGenerateArchiveFileInSubFolder(){
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        $archive_path = $tmp_folder."/archive.tar.gz";

        $fluxData = $this->getMockBuilder("FluxData")->disableOriginalConstructor()->getMock();

        $fluxData->expects($this->any())->method('getFilelist')->willReturn([[
            'key' => 'fichier',
            'filename' => 'fixtures/connecteur_exemple.yml',
            'filepath' => __DIR__.'/fixtures/connecteur_exemple.yml',
        ]]);

        /** @var FluxData $fluxData */

        $sedaNG = new SedaNG();
        $sedaNG->generateArchive($fluxData,$archive_path);

        exec("tar xvzf $archive_path -C $tmp_folder");
        $tmp_content = scandir($tmp_folder."/fixtures/");
        $this->assertEquals('connecteur_exemple.yml',$tmp_content[2]);
        $this->assertFileEquals(
            __DIR__.'/fixtures/connecteur_exemple.yml',
            $tmp_folder."/fixtures/$tmp_content[2]"
        );
        $tmpFolder->delete($tmp_folder);
    }

	/**
	 * @throws Exception
	 */
    public function testgetProprietePastellConnecteur(){
		$connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
		$connecteurConfig->addFileFromCopy('schema_rng','shema.rng',__DIR__."/fixtures/connecteur_info_schema.rng");
		$connecteurConfig->addFileFromCopy('profil_agape','profil_agape.xml',__DIR__."/fixtures/connecteur_info.xml");
		$sedaNG = new SedaNG();
		$sedaNG->setConnecteurConfig($connecteurConfig);

		$info = $sedaNG->getProprietePastellConnecteur();
		$this->assertEquals(['id_service_archive','id_producteur_hors_rh','id_producteur_rh'],$info);
	}



}