<?php

class GlaneurActesTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testTriggerOnChangeOnGlanageModeManifest()
    {
        $tmpFolder = new TmpFolder();
        $workspace_folder = $tmpFolder->create();
        $this->getObjectInstancier()->setInstance('workspacePath', $workspace_folder);
        $tmp_folder = $tmpFolder->create();
        $archive_path = $tmp_folder . "/archive.zip";

        $archive = new ZipArchive();
        $archive->open($archive_path, ZipArchive::CREATE);
        foreach (scandir(__DIR__ . "/fixtures/actes-automatique/") as $file) {
            if (in_array($file, ['.','..'])) {
                continue;
            }
            $archive->addFile(__DIR__ . "/fixtures/actes-automatique/$file", $file);
        }
        $archive->close();


        $id_ce = $this->createConnector('fakeTdt', "Bouchon Tdt")['id_ce'];

        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
        $connecteurDonneesFormulaire->addFileFromCopy(
            'classification_file',
            "classification.xml",
            __DIR__ . "/../../../../documentation/data-exemple/classification.xml"
        );
        $this->associateFluxWithConnector($id_ce, "actes-automatique", "TdT");

        $id_ce = $this->createConnector('glaneur-sftp', "Glaneur SFTP")['id_ce'];

        $this->configureConnector($id_ce, [
            "traitement_actif" =>  "on",
            "type_depot" => "ZIP",
            "manifest_type" => "xml",
            "action_ok" => "prepare-iparapheur",
            "action_ko" => "fatal-error",
        ]);
        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
        $connecteurDonneesFormulaire->addFileFromCopy(
            'fichier_exemple',
            "archive.zip",
            $archive_path
        );


        $this->triggerActionOnConnector($id_ce, 'recuperation-test');

        $documentEntite = $this->getObjectInstancier()->getInstance(DocumentEntite::class);
        $id_d = $documentEntite->getDocument(self::ID_E_COL, "actes-automatique")[0]['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertEquals(
            "Maintenance, assistance, prestations de service, formation, acquisition et extension de licences",
            $donneesFormulaire->get('objet')
        );

        $this->assertEquals('3 fichier(s) typé(s)', $donneesFormulaire->get('type_piece'));
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . "/fixtures/expected_type_piece_fichier.json",
            $donneesFormulaire->getFileContent('type_piece_fichier')
        );
        $this->assertFileEquals(
            __DIR__ . "/fixtures/actes-automatique/test-pastell-i-parapheur.pdf",
            $donneesFormulaire->getFilePath('arrete')
        );

        $tmpFolder->delete($workspace_folder);
        $tmpFolder->delete($tmp_folder);
    }

    public function testTriggerOnChangeOnGlanageModeFilematcher()
    {
        $tmpFolder = new TmpFolder();
        $workspace_folder = $tmpFolder->create();
        $this->getObjectInstancier()->setInstance('workspacePath', $workspace_folder);
        $tmp_folder = $tmpFolder->create();
        $archive_path = $tmp_folder . "/archive.zip";

        $archive = new ZipArchive();
        $archive->open($archive_path, ZipArchive::CREATE);
        foreach (scandir(__DIR__ . "/fixtures/actes-automatique/") as $file) {
            if (in_array($file, ['.','..'])) {
                continue;
            }
            $archive->addFile(__DIR__ . "/fixtures/actes-automatique/$file", $file);
        }
        $archive->close();


        $id_ce = $this->createConnector('fakeTdt', "Bouchon Tdt")['id_ce'];

        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
        $connecteurDonneesFormulaire->addFileFromCopy(
            'classification_file',
            "classification.xml",
            __DIR__ . "/../../../../documentation/data-exemple/classification.xml"
        );
        $this->associateFluxWithConnector($id_ce, "actes-automatique", "TdT");

        $id_ce = $this->createConnector('glaneur-sftp', "Glaneur SFTP")['id_ce'];

        $this->configureConnector($id_ce, [
            "traitement_actif" =>  "on",
            "type_depot" => "",
            "flux_name" => "actes-automatique",
            "action_ok" => "prepare-iparapheur",
            "action_ko" => "fatal-error",
            "file_preg_match" => "arrete: #^vide1.pdf#\nautre_document_attache:  #^vide2.pdf#",
            'metadata_static' => <<<EOT
acte_nature: 3
numero_de_lacte: 20200520
objet: Test typologie
date_de_lacte: 2020-05-20
classification: 4.2 - Personnel contractuel
type_acte: 99_AI
type_pj: ["99_AI"]
envoi_tdt: true
EOT,
        ]);
        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
        $connecteurDonneesFormulaire->addFileFromCopy(
            'fichier_exemple',
            "archive.zip",
            $archive_path
        );


        $this->triggerActionOnConnector($id_ce, 'recuperation-test');
        $documentEntite = $this->getObjectInstancier()->getInstance(DocumentEntite::class);
        $id_d = $documentEntite->getDocument(self::ID_E_COL, "actes-automatique")[0]['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertEquals(
            "Test typologie",
            $donneesFormulaire->get('objet')
        );

        $this->assertEquals('2 fichier(s) typé(s)', $donneesFormulaire->get('type_piece'));

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . "/fixtures/expected_type_piece_fichier_filematcher_mode.json",
            $donneesFormulaire->getFileContent('type_piece_fichier')
        );
        $this->assertFileEquals(
            __DIR__ . "/fixtures/actes-automatique/vide1.pdf",
            $donneesFormulaire->getFilePath('arrete')
        );

        $tmpFolder->delete($workspace_folder);
        $tmpFolder->delete($tmp_folder);
    }
}
