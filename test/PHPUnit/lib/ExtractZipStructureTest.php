<?php

require_once __DIR__ . "/../../../lib/ExtractZipStructure.class.php";



class ExtractZipStructureTest  extends \PHPUnit\Framework\TestCase {

    /**
     * @throws Exception
     * @throws UnrecoverableException
     */
    public function testExtact() {

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        $myZipArchive = new MyZipArchive();
        $myZipArchive->zipdir(__DIR__ . "/../fixtures/test_extract_zip_structure",$tmp_folder."/archive.zip");


        $FileArchiveContent = new ExtractZipStructure();

        $info = $FileArchiveContent->extract(
            $tmp_folder."/archive.zip"
        );

        unset($info['tmp_folder']);


        $expected = array (
			'root_directory' => '7756W3_9',
			'folder' =>
				array (
					0 =>
						array (
							0 => '7756W03_Rencontres_avec_PCG',
							1 => '7756W05_Assemblee_departementale_Groupe_socialiste',
							2 => '7756W06_Suivi_directions_de_la_DGA',
						),
					1 =>
						array (
							0 => '7756W03_Annee_2012',
						),
					2 =>
						array (
						),
					3 =>
						array (
						),
					4 =>
						array (
						),
				),
			'folder_name' =>
				array (
					0 => '7756W03_Rencontres_avec_PCG',
					1 => '7756W03_Annee_2012',
					2 => '7756W05_Assemblee_departementale_Groupe_socialiste',
					3 => '7756W06_Suivi_directions_de_la_DGA',
				),
			'document' =>
				array (
					0 =>
						array (
							0 => '/7756W03_Rencontres_avec_PCG/7756W03_Annee_2012/20120718_AU_20120719_President.txt',
							1 => '/7756W03_Rencontres_avec_PCG/7756W03_Annee_2012/20121001_AU_20121003_President.txt',
						),
					1 =>
						array (
						),
					2 =>
						array (
							0 => '/7756W05_Assemblee_departementale_Groupe_socialiste/20131017_AU_20150312_seances_Commission_permanente.pdf',
							1 => '/7756W05_Assemblee_departementale_Groupe_socialiste/20131023_AU_20150323_seances_Conseil_general.pdf',
						),
					3 =>
						array (
						),
					4 =>
						array (
							0 => '/7756_Bordereau_versement.pdf',
							1 => '/fichier.txt',
						),
				),
			'file_list' =>
				array (
					0 => '7756W03_Rencontres_avec_PCG/7756W03_Annee_2012/20120718_AU_20120719_President.txt',
					1 => '7756W03_Rencontres_avec_PCG/7756W03_Annee_2012/20121001_AU_20121003_President.txt',
					2 => '7756W05_Assemblee_departementale_Groupe_socialiste/20131017_AU_20150312_seances_Commission_permanente.pdf',
					3 => '7756W05_Assemblee_departementale_Groupe_socialiste/20131023_AU_20150323_seances_Conseil_general.pdf',
					4 => '7756_Bordereau_versement.pdf',
					5 => 'fichier.txt',
				)
		);

        $this->assertEquals(
            $expected,$info
        );
    }

	/**
	 * @throws Exception
	 * @throws UnrecoverableException
	 */
	public function testExtactTooManyRecusion() {

		$tmpFolder = new TmpFolder();
		$tmp_folder = $tmpFolder->create();
		$tmp = $tmp_folder;
		for($i=0;$i<11;$i++){
			$tmp = $tmp."/folder$i/";
			mkdir($tmp);
		}

		$myZipArchive = new MyZipArchive();
		$myZipArchive->zipdir($tmp_folder,$tmp_folder."/archive.zip");


		$FileArchiveContent = new ExtractZipStructure();

		$this->expectException(UnrecoverableException::class);
		$this->expectExceptionMessage(
			"Il y a plus de 10 sous-niveaux de répertoire, impossible de générer le bordereau"
		);
		$FileArchiveContent->extract(
			$tmp_folder."/archive.zip"
		);
	}

}