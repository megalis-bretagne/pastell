<?php

class FakeIparapheurTest extends PastellTestCase
{
    private const ACTES_GENERIQUE = 'actes-generique';

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testDepositSignedFileBeforeAction(): void
    {
        $connector = $this->createConnector('fakeIparapheur', 'fakeIparapheur');
        $this->configureConnector($connector['id_ce'], [
            'iparapheur_retour' => 'Archive'
        ]);
        $this->associateFluxWithConnector($connector['id_ce'], self::ACTES_GENERIQUE, 'signature');

        $document = $this->createDocument(self::ACTES_GENERIQUE);
        $id_d = $document['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData(
            [
                'envoi_signature' => true,
                'acte_nature' => 1,
                'numero_de_lacte' => 'TEST1234',
                'objet' => 'object',
                'classification' => '1.1',
                'iparapheur_type' => 'TYPE',
                'iparapheur_sous_type' => 'SOUS_TYPE'
            ]
        );
        $donneesFormulaire->addFileFromData('arrete', 'arrete.pdf', '%PDF1-4');
        $donneesFormulaire->addFileFromData('signature', 'signature.pdf', '%PDF1-4');
        $donneesFormulaire->addFileFromData('document_signe', 'document_signe.pdf', '%PDF1-4');

        $this->triggerActionOnDocument($id_d, 'send-iparapheur');
        $this->triggerActionOnDocument($id_d, 'verif-iparapheur');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertSame('%PDF1-4', $donneesFormulaire->getFileContent('signature'));
        $this->assertSame('signature.pdf', $donneesFormulaire->getFileName('signature'));
        $this->assertSame('%PDF1-4', $donneesFormulaire->getFileContent('document_signe'));
        $this->assertSame('document_signe.pdf', $donneesFormulaire->getFileName('document_signe'));
    }
}
