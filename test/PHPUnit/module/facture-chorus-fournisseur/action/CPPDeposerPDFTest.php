<?php

class CPPDeposerPDFTest extends ExtensionCppTestCase
{
    /**
     * @throws NotFoundException
     */
    public function testDeposerPDF()
    {

        $deposer_pdf_result =  [
            'codeRetour' => 0,
            'libelle' => 'GCU_MSG_01_000',
            'numeroFacture' => null,
            'dateFacture' => null,
            'codeDestinataire' => null,
            'codeServiceExecutant' => null,
            'codeFournisseur' => null,
            'codeDeviseFacture' => null,
            'typeFacture' => 'FACTURE',
            'typeTva' => null,
            'numeroBonCommande' => null,
            'montantTtcAvantRemiseGlobalTTC' => null,
            'montantAPayer' => null,
            'montantHtTotal' => null,
            'montantTVA' => null,
            'pieceJointeId' => 6921279
        ];

        $cppWrapper = $this->getMockBuilder(CPPWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cppWrapper->expects($this->any())->method('call')->willReturn($deposer_pdf_result);

        $cppWrapperFactory = $this->getMockBuilder(CPPWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapperFactory->expects($this->any())->method('newInstance')->willReturn($cppWrapper);

        $this->getObjectInstancier()->setInstance(CPPWrapperFactory::class, $cppWrapperFactory);


        $this->createCppConnector("facture-chorus-fournisseur");
        $document = $this->createDocument("facture-chorus-fournisseur");

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);

        $donneesFormulaire->addFileFromCopy('fichier_facture_pdf', 'Facture-PDF.pdf', self::FICHIER_FACTURE_PDF);

        $this->triggerActionOnDocument($document['id_d'], 'deposer-pdf');
        $this->expectOutputRegex("#Document/edition#");

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);

        $this->assertEquals(1, $donneesFormulaire->get('has_information'));
        $this->assertTrue($donneesFormulaire->isEditable('numero_facture'));
    }
}
