<?php

require_once __DIR__ . "/../../../lib/ExtractZipStructure.class.php";



class ExtractZipStructureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws Exception
     * @throws UnrecoverableException
     */
    public function testExtract()
    {

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        $myZipArchive = new MyZipArchive();
        $myZipArchive->zipdir(__DIR__ . "/../fixtures/test_extract_zip_structure", $tmp_folder . "/archive.zip");


        $FileArchiveContent = new ExtractZipStructure();

        $info = $FileArchiveContent->extract(
            $tmp_folder . "/archive.zip"
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
            'file' =>
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
            $expected,
            $info
        );
    }

    /**
     * @throws Exception
     * @throws UnrecoverableException
     */
    public function testExtractTooManyRecusion()
    {

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        $tmp = $tmp_folder;
        for ($i = 0; $i < ExtractZipStructure::MAX_RECURSION_LEVEL + 1; $i++) {
            $tmp = $tmp . "/folder$i/";
            mkdir($tmp);
        }

        $myZipArchive = new MyZipArchive();
        $myZipArchive->zipdir($tmp_folder, $tmp_folder . "/archive.zip");


        $FileArchiveContent = new ExtractZipStructure();


        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Il y a plus de %d sous-niveaux de répertoire, impossible de générer le bordereau",
                ExtractZipStructure::MAX_RECURSION_LEVEL
            )
        );
        $FileArchiveContent->extract(
            $tmp_folder . "/archive.zip"
        );
    }

    /**
     * @throws Exception
     */
    public function testExtractStopRecursion()
    {
        $FileArchiveContent = new ExtractZipStructure();
        $FileArchiveContent->setNbRecusionLevelStop(3);
        $data = $FileArchiveContent->extract(__DIR__ . "/../fixtures/test_extract_zip_structure_stop_recursion.zip");

        unset($data['tmp_folder']);

        $this->assertEquals(
            array (
                'root_directory' => '42007_achat_de_materiel_de_bureau',
                'folder' =>
                    array (
                        0 =>
                            array (
                                0 => 'DCE',
                                1 => 'OFFRES DEMAT',
                            ),
                        1 =>
                            array (
                            ),
                        2 =>
                            array (
                                0 => 'ADULLACT',
                                1 => 'LIBRICIEL',
                                2 => 'SIGMALIS',
                            ),
                        3 =>
                            array (
                            ),
                        4 =>
                            array (
                            ),
                        5 =>
                            array (
                                0 => 'Sous-rep 1',
                            ),
                        6 =>
                            array (
                            ),
                    ),
                'folder_name' =>
                    array (
                        0 => 'DCE',
                        1 => 'OFFRES DEMAT',
                        2 => 'ADULLACT',
                        3 => 'LIBRICIEL',
                        4 => 'SIGMALIS',
                        5 => 'Sous-rep 1',
                    ),
                'file' =>
                    array (
                        0 =>
                            array (
                                0 => '/DCE/rougon-macquart.pdf',
                                1 => '/DCE/vide.pdf',
                            ),
                        1 =>
                            array (
                                0 => '/OFFRES DEMAT/ADULLACT/IMG_0355.jpg',
                                1 => '/OFFRES DEMAT/ADULLACT/foo.txt',
                            ),
                        2 =>
                            array (
                            ),
                        3 =>
                            array (
                                0 => '/OFFRES DEMAT/SIGMALIS/Sous-rep 1/sous-rep-2/pam.txt',
                                1 => '/OFFRES DEMAT/SIGMALIS/Sous-rep 1/toto.txt',
                            ),
                        4 =>
                            array (
                                0 => '/OFFRES DEMAT/SIGMALIS/offre1.pdf',
                                1 => '/OFFRES DEMAT/SIGMALIS/offre2.odt',
                            ),
                        5 =>
                            array (
                                0 => '/OFFRES DEMAT/README.txt',
                            ),
                        6 =>
                            array (
                            ),
                    ),
                'file_list' =>
                    array (
                        0 => 'DCE/rougon-macquart.pdf',
                        1 => 'DCE/vide.pdf',
                        2 => 'OFFRES DEMAT/ADULLACT/IMG_0355.jpg',
                        3 => 'OFFRES DEMAT/ADULLACT/foo.txt',
                        4 => 'OFFRES DEMAT/SIGMALIS/Sous-rep 1/sous-rep-2/pam.txt',
                        5 => 'OFFRES DEMAT/SIGMALIS/Sous-rep 1/toto.txt',
                        6 => 'OFFRES DEMAT/SIGMALIS/offre1.pdf',
                        7 => 'OFFRES DEMAT/SIGMALIS/offre2.odt',
                        8 => 'OFFRES DEMAT/README.txt',
                    ),
            ),
            $data
        );
    }
}
