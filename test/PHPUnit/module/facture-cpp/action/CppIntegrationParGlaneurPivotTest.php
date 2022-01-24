<?php

class CppIntegrationParGlaneurPivotTest extends ExtensionCppTestCase
{
    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testGlanerPivot()
    {
        $glaneurSFTP = $this->getObjectInstancier()->getInstance(GlaneurSFTP::class);
        $glaneurSFTP->setLogger($this->getLogger());
        $glaneurSFTP->setConnecteurInfo(['id_e' => 1]);
        $collectiviteProperties = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $collectiviteProperties->setTabData([
            GlaneurConnecteur::TRAITEMENT_ACTIF => '1',
            GlaneurConnecteur::TYPE_DEPOT => GlaneurConnecteur::TYPE_DEPOT_ZIP,
            GlaneurConnecteur::FILE_PREG_MATCH => 'fichier_facture: /^(.*)-pivot.xml$/',
            GlaneurConnecteur::FLUX_NAME => 'facture-cpp',
            GlaneurConnecteur::ACTION_OK => 'importation-glaneur-pivot',

        ]);
        $collectiviteProperties->addFileFromCopy(
            GlaneurConnecteur::FICHER_EXEMPLE,
            'pivot.zip',
            self::PIVOT_ZIP
        );
        $glaneurSFTP->setConnecteurConfig($collectiviteProperties);

        $id_d = $glaneurSFTP->glanerFicExemple();
        $this->assertSame("Création du document $id_d", $glaneurSFTP->getLastMessage()[0]);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $this->assertSame(
            'facture-pivot.xml',
            $donneesFormulaire->getFileName('fichier_facture')
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testExtrairePivotIsEditable()
    {

        $tmpFolder = $this->getMockBuilder(TmpFolder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tmpFolder->expects($this->any())->method('create')->willReturn(self::TMP_EXTRACTED);
        $tmpFolder->expects($this->any())->method('delete')->willReturn(true);
        $this->getObjectInstancier()->setInstance(TmpFolder::class, $tmpFolder);


        $zip = $this->getMockBuilder(ZipArchive::class)
            ->disableOriginalConstructor()
            ->getMock();
        $zip->expects($this->any())->method('open')->willReturn(true);
        $zip->expects($this->any())->method('extractTo')->willReturn(true);
        $zip->expects($this->any())->method('close')->willReturn(true);
        $this->getObjectInstancier()->setInstance(ZipArchive::class, $zip);


        $this->createParamChorusConnector("facture-cpp");
        $document = $this->createDocument("facture-cpp");

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->addFileFromCopy('fichier_facture', 'facture-pivot.xml', self::FICHIER_PIVOT);

        $result = $this->triggerActionOnDocument($document['id_d'], 'integration-glaneur-pivot');

        $this->assertTrue($result);
        $this->assertLastMessage('Intégration du dossier via le fichier PIVOT');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);

        $this->assertEquals("FAC19-2512", $donneesFormulaire->get('no_facture'));
    }
}
