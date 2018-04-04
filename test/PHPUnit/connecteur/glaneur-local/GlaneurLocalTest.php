<?php

require_once __DIR__."/../../../../connecteur/glaneur-local/GlaneurLocal.class.php";

class GlaneurLocalTest extends PastellTestCase {

    /** @var  TmpFolder */
    private $tmpFolder;
    private $tmp_folder;
    private $directory_send;

    private $last_message;
    private $created_id_d;

    /** @throws Exception */
    protected function setUp() {
        parent::setUp();
        $this->tmpFolder = new TmpFolder();
        $this->tmp_folder = $this->tmpFolder->create();
        $this->directory_send = $this->tmpFolder->create();
    }

    protected function tearDown() {
        $this->tmpFolder->delete($this->tmp_folder);
        $this->tmpFolder->delete($this->directory_send);
    }

    /**
     * @param $collectivite_properties
     * @return string
     * @throws Exception */
    private function glanerWithProperties(array $collectivite_properties){
        $glaneurLocal = $this->getObjectInstancier()->getInstance("GlaneurLocal");
        $glaneurLocal->setConnecteurInfo(['id_e'=>1]);
        $collectiviteProperties = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $collectiviteProperties->setTabData($collectivite_properties);
        $glaneurLocal->setConnecteurConfig($collectiviteProperties);
        $result = $glaneurLocal->glaner();
        $this->last_message = $glaneurLocal->getLastMessage();
        $this->created_id_d = $glaneurLocal->getCreatedId_d();
        return $result;
    }

    /**
     * @throws Exception
     */
    public function testGlanerNotExistingFlux(){
        mkdir($this->tmp_folder."/"."test1");
        copy(__DIR__."/fixtures/foo.txt",$this->tmp_folder."/"."test1/foo.txt");

        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage("Impossible de trouver le type not-existing-flux sur ce pastell");
        $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::FLUX_NAME => 'not-existing-flux',
            GlaneurLocal::FILE_PREG_MATCH => 'fichier_pes: #.*#',
        ]);

    }

    /**
     * @throws Exception
     */
    public function testGlanerNotExistingTypeDepot(){
        mkdir($this->tmp_folder."/"."test1");
        copy(__DIR__."/fixtures/foo.txt",$this->tmp_folder."/"."test1/foo.txt");

        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage("Le type de dépot est inconnu");
        $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => "foo",
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::FLUX_NAME => 'helios-automatique',
            GlaneurLocal::FILE_PREG_MATCH => 'fichier_pes: #.*#',
        ]);

    }


    /** @throws Exception */
    public function testGlanerDirectoryEmpty(){
        $this->expectExceptionMessage("Directory name must not be empty.");
        $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER
        ]);
    }

    /** @throws Exception */
    public function testGlanerDirectoryNotFound(){
        $this->expectExceptionMessage("DirectoryIterator::__construct(foo): failed to open dir: No such file or directory");
        $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
            GlaneurLocal::DIRECTORY => 'foo'
        ]);
    }

    /** @throws Exception */
    public function testGlanerEmptyDirectory(){
        $this->assertTrue($this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
            GlaneurLocal::DIRECTORY => $this->tmp_folder
        ]));
        $this->assertEquals(["Le répertoire est vide"],$this->last_message);
    }

    /** @throws Exception */
    public function testGlanerFolderFileNoFlux(){
        mkdir($this->tmp_folder."/"."test1");
        copy(__DIR__."/fixtures/foo.txt",$this->tmp_folder."/"."test1/foo.txt");
        $this->expectExceptionMessage("Impossible de trouver le nom du flux à créer");
        $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
            GlaneurLocal::DIRECTORY => $this->tmp_folder
        ]);
    }

    /** @throws Exception */
    public function testGlanerOneFile(){
        mkdir($this->tmp_folder."/"."test1");
        copy(__DIR__."/fixtures/foo.txt",$this->tmp_folder."/"."test1/foo.txt");
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le formulaire est incomplet : le champ «Nature de l'acte» est obligatoire.");
        $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::FILE_PREG_MATCH => 'arrete: #.*#',
            GlaneurLocal::FLUX_NAME => 'actes-generique',
            GlaneurLocal::ACTION_OK => 'send-tdt'

        ]);
    }

    /**
     * @throws Exception
     */
    public function testGlanerPES(){
        mkdir($this->tmp_folder."/"."test1");
        copy(
            __DIR__."/fixtures/HELIOS_SIMU_ALR2_1514362287_770650402.xml",
            $this->tmp_folder."/"."test1/test.xml"
        );

        $this->assertTrue(
            $this->glanerWithProperties([
                GlaneurLocal::TRAITEMENT_ACTIF => '1',
                GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
                GlaneurLocal::DIRECTORY => $this->tmp_folder,
                GlaneurLocal::DIRECTORY_SEND  => $this->directory_send,
                GlaneurLocal::FLUX_NAME => 'helios-automatique',
                GlaneurLocal::METADATA_STATIC => 'objet:Bordereau de test',
                GlaneurLocal::FILE_PREG_MATCH => 'fichier_pes: #.*#',
                GlaneurLocal::ACTION_OK => 'importation',
                GlaneurLocal::ACTION_KO => 'erreur'
            ])
        );

        $this->assertRegExp("#Création du document#",$this->last_message[0]);
        $id_d = $this->created_id_d[0];

        $document = $this->getObjectInstancier()->getInstance("Document");
        $info = $document->getInfo($id_d);
        $this->assertEquals("Bordereau de test",$info['titre']);
        $this->assertEquals("helios-automatique",$info['type']);

        $journal = $this->getJournal()->getAll(1,'helios-automatique',$id_d,0,0,100);
        $this->assertEquals("[glaneur] Passage en action_ok : importation",$journal[0]['message']);
        $this->assertEquals("[glaneur] Import du document",$journal[1]['message']);


        $donneesFormulaireFactory = $this->getObjectInstancier()->getInstance("DonneesFormulaireFactory");
        $donneesFormulaire = $donneesFormulaireFactory->get($id_d);
        $this->assertEquals("Bordereau de test",$donneesFormulaire->get('objet'));
        $this->assertFileEquals(
            __DIR__."/fixtures/HELIOS_SIMU_ALR2_1514362287_770650402.xml",
            $donneesFormulaire->getFilePath('fichier_pes')
        );
        $this->assertFileNotExists($this->tmp_folder."/"."test1");
        $this->assertFileExists($this->directory_send."/"."test1");
    }

	/**
	 * @throws Exception
	 */
	public function testGlanerDirectoryWithManyFiles(){

		mkdir($this->tmp_folder."/"."test1");
		$src = __DIR__."/fixtures/many_files";
		$dest = $this->tmp_folder."/"."test1";
		`cp  $src/* $dest`;


		$this->glanerWithProperties([
			GlaneurLocal::TRAITEMENT_ACTIF => '1',
			GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
			GlaneurLocal::DIRECTORY => $this->tmp_folder,
			GlaneurLocal::DIRECTORY_SEND  => $this->directory_send,
			GlaneurLocal::FLUX_NAME => 'test',
			GlaneurLocal::METADATA_STATIC => 'test2:toto',
			GlaneurLocal::FILE_PREG_MATCH => 'fichier: #.*#',
			GlaneurLocal::ACTION_OK => 'importation',
			GlaneurLocal::ACTION_KO => 'erreur'
		]);

		$id_d = $this->created_id_d[0];

		$donneesFormulaireFactory = $this->getObjectInstancier()->getInstance("DonneesFormulaireFactory");
		$donneesFormulaire = $donneesFormulaireFactory->get($id_d);

		$this->assertEquals(["a.txt","b.txt","c.txt"],$donneesFormulaire->get('fichier'));
	}


    /**
     * @throws Exception
     */
    public function testMetadataWithFileName(){
        mkdir($this->tmp_folder."/"."test1");
        copy(
            __DIR__."/fixtures/HELIOS_SIMU_ALR2_1514362287_770650402.xml",
            $this->tmp_folder."/"."test1/test.xml"
        );

        $this->assertTrue(
            $this->glanerWithProperties([
                GlaneurLocal::TRAITEMENT_ACTIF => '1',
                GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
                GlaneurLocal::DIRECTORY => $this->tmp_folder,
                GlaneurLocal::FLUX_NAME => 'helios-automatique',
                GlaneurLocal::METADATA_STATIC => 'objet: %fichier_pes%',
                GlaneurLocal::FILE_PREG_MATCH => 'fichier_pes: #.*#',
                GlaneurLocal::ACTION_OK => 'importation',
                GlaneurLocal::ACTION_KO => 'erreur'
            ])
        );

        $this->assertRegExp("#Création du document#",$this->last_message[0]);
        $id_d = $this->created_id_d[0];

        $document = $this->getObjectInstancier()->getInstance("Document");
        $info = $document->getInfo($id_d);
        $this->assertEquals("test.xml",$info['titre']);

        $donneesFormulaireFactory = $this->getObjectInstancier()->getInstance("DonneesFormulaireFactory");
        $donneesFormulaire = $donneesFormulaireFactory->get($id_d);
        $this->assertEquals("test.xml",$donneesFormulaire->get('objet'));
    }

    /**
     * @throws Exception
     */
    public function testMetadataWithBadFileName(){
        mkdir($this->tmp_folder."/"."test1");
        copy(
            __DIR__."/fixtures/HELIOS_SIMU_ALR2_1514362287_770650402.xml",
            $this->tmp_folder."/"."test1/test.xml"
        );

        $this->expectExceptionMessage("not-existing-element n'a pas été trouvé dans la correspondance des fichiers");
        $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::FLUX_NAME => 'helios-automatique',
            GlaneurLocal::METADATA_STATIC => 'objet: %not-existing-element%',
            GlaneurLocal::FILE_PREG_MATCH => 'fichier_pes: #.*#',
            GlaneurLocal::ACTION_OK => 'importation',
            GlaneurLocal::ACTION_KO => 'erreur'
        ]);
    }

    /**
     * @throws Exception
     */
    public function testGlanerDeleteFolder(){
        mkdir($this->tmp_folder."/"."test1");
        copy(
            __DIR__."/fixtures/HELIOS_SIMU_ALR2_1514362287_770650402.xml",
            $this->tmp_folder."/"."test1/test.xml"
        );

        $this->assertTrue(
            $this->glanerWithProperties([
                GlaneurLocal::TRAITEMENT_ACTIF => '1',
                GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
                GlaneurLocal::DIRECTORY => $this->tmp_folder,
                GlaneurLocal::FLUX_NAME => 'helios-automatique',
                GlaneurLocal::METADATA_STATIC => 'objet:Bordereau de test',
                GlaneurLocal::FILE_PREG_MATCH => 'fichier_pes: #.*#',
                GlaneurLocal::ACTION_OK => 'importation',
                GlaneurLocal::ACTION_KO => 'erreur'
            ])
        );


        $this->assertFileNotExists($this->tmp_folder."/"."test1");
        $this->assertFileNotExists($this->directory_send."/"."test1");
    }


    /**
     * @throws Exception
     */
    public function testGlanerDepotVrac(){

        $fixtures_dir = __DIR__."/fixtures/pes_depot_vrac/";
        foreach(scandir($fixtures_dir) as $file){
            if (is_file($fixtures_dir."/".$file)) {
                copy($fixtures_dir . "/" . $file, $this->tmp_folder."/$file");
            }
        }

        $this->assertTrue( $this->glanerWithProperties([
                GlaneurLocal::TRAITEMENT_ACTIF => '1',
                GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_VRAC,
                GlaneurLocal::DIRECTORY => $this->tmp_folder,
                GlaneurLocal::DIRECTORY_SEND => $this->directory_send,
                GlaneurLocal::FLUX_NAME => 'helios-automatique',
                GlaneurLocal::FILE_PREG_MATCH =>  'fichier_pes: #^(PESALR2.*)$#'."\n" .'fichier_reponse:#^ACQUIT_$matches[0][1]$#',
                GlaneurLocal::METADATA_STATIC => "objet:%fichier_pes%\nenvoi_sae:true\nhas_information_complementaire:true",
                GlaneurLocal::ACTION_OK => 'importation',
                GlaneurLocal::ACTION_KO => 'erreur'
            ]));

        $this->assertRegExp("#Création du document#",$this->last_message[0]);

        $id_d = $this->created_id_d[0];
        $document = $this->getObjectInstancier()->getInstance("Document");
        $info = $document->getInfo($id_d);
        $this->assertEquals("PESALR2_49101169800000_171227_2045.xml",$info['titre']);
        $this->assertEquals("helios-automatique",$info['type']);

        $donneesFormulaireFactory = $this->getObjectInstancier()->getInstance("DonneesFormulaireFactory");
        $donneesFormulaire = $donneesFormulaireFactory->get($id_d);
        $this->assertEquals("PESALR2_49101169800000_171227_2045.xml",$donneesFormulaire->get('objet'));
        $this->assertFileEquals(
            __DIR__."/fixtures/pes_depot_vrac/PESALR2_49101169800000_171227_2045.xml",
            $donneesFormulaire->getFilePath('fichier_pes')
        );

        $this->assertFileExists($this->directory_send."/PESALR2_49101169800000_171227_2045.xml");
        $this->assertFileExists($this->directory_send."/ACQUIT_PESALR2_49101169800000_171227_2045.xml");
        $this->assertFileNotExists($this->tmp_folder."/PESALR2_49101169800000_171227_2045.xml");

        $this->assertTrue( $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_VRAC,
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::DIRECTORY_SEND => $this->directory_send,
            GlaneurLocal::FLUX_NAME => 'helios-automatique',
            GlaneurLocal::FILE_PREG_MATCH =>  'fichier_pes: #^(PESALR2.*)$#'."\n" .'fichier_reponse:#ACQUIT_$matches[0][1]#',
            GlaneurLocal::METADATA_STATIC => "objet:%fichier_pes%\nenvoi_sae:true\nhas_information_complementaire:true",
            GlaneurLocal::ACTION_OK => 'importation',
            GlaneurLocal::ACTION_KO => 'erreur'
        ]));
        $this->assertRegExp("#Création du document#",$this->last_message[0]);

        $id_d = $this->created_id_d[1];
        $document = $this->getObjectInstancier()->getInstance("Document");
        $info = $document->getInfo($id_d);
        $this->assertEquals("PESALR2_49101169800000_171227_2047.xml",$info['titre']);
        $this->assertEquals("helios-automatique",$info['type']);


    }

    /**
     * @throws Exception
     */
    public function testGlanerVracEmpty(){
        $this->assertTrue( $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_VRAC,
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::DIRECTORY_SEND => $this->directory_send,
            GlaneurLocal::FLUX_NAME => 'helios-automatique',
            GlaneurLocal::FILE_PREG_MATCH =>  'fichier_pes: #^(PESALR2.*)$#'."\n" .'fichier_reponse:#ACQUIT_$matches[1][1]#',
            GlaneurLocal::METADATA_STATIC => "objet:%fichier_pes%\nenvoi_sae:true\nhas_information_complementaire:true",
            GlaneurLocal::ACTION_OK => 'importation',
            GlaneurLocal::ACTION_KO => 'erreur'
        ]));
        $this->assertRegExp("#Le répertoire est vide#",$this->last_message[0]);
    }

    /**
     * @throws Exception
     */
    public function testGlanerZip(){
        copy(__DIR__ . "/fixtures/pes_exemple.zip", $this->tmp_folder."/pes_exemple.zip");

        $this->assertTrue( $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_ZIP,
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::DIRECTORY_SEND => $this->directory_send,
            GlaneurLocal::FLUX_NAME => 'helios-automatique',
            GlaneurLocal::FILE_PREG_MATCH =>  'fichier_pes: #^(PESALR2.*)$#'."\n" .'fichier_reponse:#ACQUIT_$matches[1][1]#',
            GlaneurLocal::METADATA_STATIC => "objet:%fichier_pes%\nenvoi_sae:true\nhas_information_complementaire:true",
            GlaneurLocal::ACTION_OK => 'importation',
            GlaneurLocal::ACTION_KO => 'erreur'
        ]));

        $this->assertRegExp("#Création du document#",$this->last_message[0]);

        $id_d = $this->created_id_d[0];
        $document = $this->getObjectInstancier()->getInstance("Document");
        $info = $document->getInfo($id_d);
        $this->assertEquals("PESALR2_49101169800000_171227_2045.xml",$info['titre']);
        $this->assertEquals("helios-automatique",$info['type']);
        $this->assertFileExists($this->directory_send."/pes_exemple.zip");
        $this->assertFileNotExists($this->tmp_folder."/pes_exemple.zip");
    }

    /**
     * @throws Exception
     */
    public function testMenageExists(){
        copy(__DIR__ . "/fixtures/pes_exemple.zip", $this->tmp_folder."/pes_exemple.zip");
        copy(__DIR__ . "/fixtures/pes_exemple.zip", $this->directory_send."/pes_exemple.zip");

        $this->assertTrue( $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_ZIP,
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::DIRECTORY_SEND => $this->directory_send,
            GlaneurLocal::FLUX_NAME => 'helios-automatique',
            GlaneurLocal::FILE_PREG_MATCH =>  'fichier_pes: #^(PESALR2.*)$#'."\n" .'fichier_reponse:#ACQUIT_$matches[1][1]#',
            GlaneurLocal::METADATA_STATIC => "objet:%fichier_pes%\nenvoi_sae:true\nhas_information_complementaire:true",
            GlaneurLocal::ACTION_OK => 'importation',
            GlaneurLocal::ACTION_KO => 'erreur'
        ]));

        $this->assertFileExists($this->directory_send."/pes_exemple.zip-0");
        $this->assertFileNotExists($this->tmp_folder."/pes_exemple.zip");
    }

    /**
     * @throws Exception
     */
    public function testGlanerZipEmptyFolder(){

        $this->assertTrue( $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_ZIP,
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::DIRECTORY_SEND => $this->directory_send,
            GlaneurLocal::FLUX_NAME => 'helios-automatique',
            GlaneurLocal::FILE_PREG_MATCH =>  'fichier_pes: #^(PESALR2.*)$#'."\n" .'fichier_reponse:#ACQUIT_$matches[1][1]#',
            GlaneurLocal::METADATA_STATIC => "objet:%fichier_pes%\nenvoi_sae:true\nhas_information_complementaire:true",
            GlaneurLocal::ACTION_OK => 'importation',
            GlaneurLocal::ACTION_KO => 'erreur'
        ]));
        $this->assertRegExp("#Le répertoire est vide#",$this->last_message[0]);
    }

    /**
     * @throws Exception
     */
    public function testGlanerZipNotAZipFile(){
        copy(__DIR__ . "/fixtures/foo.txt", $this->tmp_folder."/pes_exemple.zip");

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Impossible d'ouvrir le fichier zip");

        $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_ZIP,
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::DIRECTORY_SEND => $this->directory_send,
            GlaneurLocal::FLUX_NAME => 'helios-automatique',
            GlaneurLocal::FILE_PREG_MATCH => 'fichier_pes: #^(PESALR2.*)$#' . "\n" . 'fichier_reponse:#ACQUIT_$matches[1][1]#',
            GlaneurLocal::METADATA_STATIC => "objet:%fichier_pes%\nenvoi_sae:true\nhas_information_complementaire:true",
            GlaneurLocal::ACTION_OK => 'importation',
            GlaneurLocal::ACTION_KO => 'erreur'
        ]);
    }

    /**
     * @throws Exception
     */
    public function testGlanerManifest(){

        mkdir($this->tmp_folder."/test1/");
        $fixtures_dir = __DIR__."/fixtures/pes_manifest/";
        foreach(scandir($fixtures_dir) as $file){
            if (is_file($fixtures_dir."/".$file)) {
                copy($fixtures_dir . "/" . $file, $this->tmp_folder."/test1/$file");
            }
        }

        $this->assertTrue( $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::DIRECTORY_SEND => $this->directory_send,
            GlaneurLocal::MANIFEST_TYPE => GlaneurLocal::MANIFEST_TYPE_XML,
            GlaneurLocal::ACTION_KO => 'erreur'
        ]));

        $this->assertRegExp("#Création du document#",$this->last_message[0]);

        $id_d = $this->created_id_d[0];
        $document = $this->getObjectInstancier()->getInstance("Document");
        $info = $document->getInfo($id_d);
        $this->assertEquals("Exemple d'import d'un fichier PES",$info['titre']);
        $this->assertEquals("helios-automatique",$info['type']);

        $donneesFormulaireFactory = $this->getObjectInstancier()->getInstance("DonneesFormulaireFactory");
        $donneesFormulaire = $donneesFormulaireFactory->get($id_d);
        $this->assertEquals("Exemple d'import d'un fichier PES",$donneesFormulaire->get('objet'));
        $this->assertFileEquals(
            __DIR__."/fixtures/pes_depot_vrac/PESALR2_49101169800000_171227_2045.xml",
            $donneesFormulaire->getFilePath('fichier_pes')
        );
    }

    /**
     * @throws Exception
     */
    public function testGlanerManifestNoManifest(){

        mkdir($this->tmp_folder."/test1/");
        $fixtures_dir = __DIR__."/fixtures/pes_manifest/";
        foreach(scandir($fixtures_dir) as $file){
            if (is_file($fixtures_dir."/".$file)) {
                if ($file == 'manifest.xml') {continue;}
                copy($fixtures_dir . "/" . $file, $this->tmp_folder."/test1/$file");
            }
        }

        $this->expectExceptionMessage("Le fichier manifest.xml n'existe pas");
        $this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '1',
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
            GlaneurLocal::DIRECTORY => $this->tmp_folder,
            GlaneurLocal::DIRECTORY_SEND => $this->directory_send,
            GlaneurLocal::MANIFEST_TYPE => GlaneurLocal::MANIFEST_TYPE_XML,
            GlaneurLocal::ACTION_KO => 'erreur'
        ]);

    }

    /**
     * @throws Exception
     */
    public function testGlanerNoActif(){
        $this->assertFalse($this->glanerWithProperties([
            GlaneurLocal::TRAITEMENT_ACTIF => '0',
        ]));
        $this->assertEquals(["Le traitement du glaneur est désactivé"],$this->last_message);

    }

}