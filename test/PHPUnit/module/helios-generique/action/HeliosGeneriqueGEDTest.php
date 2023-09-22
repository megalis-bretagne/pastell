<?php

class HeliosGeneriqueGEDTest extends PastellTestCase
{
    /**
     * @throws NotFoundException
     * @throws DonneesFormulaireException
     */
    public function testCasNominal()
    {
        $info_connecteur_signature = $this->createConnector('fakeIparapheur', 'Bouchon signature');
        $this->configureConnector($info_connecteur_signature['id_ce'], [
            'iparapheur_type' => 'Document',
            'iparapheur_envoi_status' => 'ok',
            'iparapheur_retour' => 'Archive',
        ]);
        $info_connecteur_GED = $this->createConnector('FakeGED', 'Bouchon GED');
        $this->associateFluxWithConnector($info_connecteur_signature['id_ce'], 'helios-generique', 'signature');
        $this->associateFluxWithConnector($info_connecteur_GED['id_ce'], 'helios-generique', 'GED');

        $info = $this->createDocument('helios-generique');

        $documentId = $info['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($documentId);
        $donneesFormulaire->setTabData([
            'objet' => 'Foo',
            'envoi_signature_check' => 'On',
            'envoi_ged' => 'On',
            'iparapheur_type' => 'test',
            'iparapheur_sous_type' => 'test',
        ]);
        $donneesFormulaire->addFileFromCopy(
            'fichier_pes',
            'HELIOS_SIMU_ALR2_1496987735_826268894.xml',
            __DIR__ . '/../fixtures/HELIOS_SIMU_ALR2_1496987735_826268894.xml'
        );

        $this->assertActionPossible(['modification','supression', 'send-ged'], $documentId);
        static::assertTrue(
            $this->triggerActionOnDocument($documentId, 'send-iparapheur')
        );
        $this->assertActionPossible(['verif-iparapheur'], $documentId);
        $this->triggerActionOnDocument($documentId, 'verif-iparapheur');
        $this->assertActionPossible(['modification','supression', 'send-ged'], $documentId);
        static::assertTrue(
            $this->triggerActionOnDocument($documentId, 'send-ged')
        );
        $this->assertActionPossible(['modification','supression'], $documentId);
    }
}
