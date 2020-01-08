<?php
require_once __DIR__ . "/../../../../../connecteur-type/glaneur/lib/GlaneurDocumentCreator.class.php";

class GlaneurDocumentCreatorTest extends PastellTestCase
{

    private const HELIOS_AUTOMATIQUE = 'helios-automatique';
    private const IMPORTATION = 'importation';

    /**
     * @throws Exception
     */
    public function testCreateDocument()
    {
        $notification = $this->getObjectInstancier()->getInstance(Notification::class);
        $notification->add(1, 1, self::HELIOS_AUTOMATIQUE, self::IMPORTATION, false);

        $glaneurLocalDocumentCreator = $this->getObjectInstancier()->getInstance('GlaneurDocumentCreator');

        $glaneurLocalDocumentInfo = new GlaneurDocumentInfo(1);
        $glaneurLocalDocumentInfo->nom_flux = self::HELIOS_AUTOMATIQUE;
        $glaneurLocalDocumentInfo->metadata = ['objet' => 'test_pes'];
        $glaneurLocalDocumentInfo->element_files_association = [
            'fichier_pes' => ['test.xml']
        ];
        $glaneurLocalDocumentInfo->action_ok = self::IMPORTATION;
        $glaneurLocalDocumentInfo->action_ko = 'erreur_import';


        $this->assertNotEmpty(
            $glaneurLocalDocumentCreator->create(
                $glaneurLocalDocumentInfo,
                __DIR__ . "/../fixtures/pes_exemple/"
            )
        );
        $journal_logs = $this->getJournal()->getAll(1, "", "", "", 0, 100);
        $this->assertEquals(
            "notification envoyée à eric@sigmalis.com",
            $journal_logs[0]['message']
        );
    }

    /**
     * @throws Exception
     */
    public function testCreateDocumentFailed()
    {
        $glaneurLocalDocumentCreator = $this->getObjectInstancier()->getInstance('GlaneurDocumentCreator');

        $glaneurLocalDocumentInfo = new GlaneurDocumentInfo(1);
        $glaneurLocalDocumentInfo->nom_flux = self::HELIOS_AUTOMATIQUE;
        $glaneurLocalDocumentInfo->metadata = [];
        $glaneurLocalDocumentInfo->element_files_association = [
            'fichier_pes' => ['test.xml']
        ];
        $glaneurLocalDocumentInfo->action_ok = self::IMPORTATION;
        $glaneurLocalDocumentInfo->action_ko = 'erreur_import';

        $this->assertNotEmpty(
            $glaneurLocalDocumentCreator->create(
                $glaneurLocalDocumentInfo,
                __DIR__ . "/../fixtures/pes_exemple/"
            )
        );
    }
}
