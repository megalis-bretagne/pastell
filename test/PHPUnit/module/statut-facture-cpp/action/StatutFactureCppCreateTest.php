<?php

require_once __DIR__ . "/../../../../../connecteur-type/PortailFactureConnecteur.class.php";
require_once __DIR__ . "/../../../../../connecteur/cpp/CPP.class.php";

class StatutFactureCppCreateTest extends ExtensionCppTestCase
{
    private const FLUX_STATUT_FACTURE_CPP = "statut-facture-cpp";
    private const ACTION_CREATE_STATUT_FACTURE_CPP = 'create-statut-facture-cpp';

    /**
     * When the supplier cannot be found on chorus
     *
     * @test
     * @throws Exception
     */
    public function whenTheSupplierIsNotFound()
    {
        $cppWrapper = $this->getMockBuilder(CPPWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapper->expects($this->any())
            ->method('getIdentifiantStructureCPPByIdentifiantStructure')
            ->willReturn(false);

        $cppWrapperFactory = $this->getMockBuilder(CPPWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapperFactory->expects($this->any())->method('newInstance')->willReturn($cppWrapper);

        $this->getObjectInstancier()->setInstance(CPPWrapperFactory::class, $cppWrapperFactory);

        $this->createCppConnector(self::FLUX_STATUT_FACTURE_CPP);
        $document = $this->createDocument(self::FLUX_STATUT_FACTURE_CPP);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->addFileFromCopy(
            'fichier_statut_facture',
            'Example01-FactureStatut-ok.xml',
            __DIR__ . "/../fixtures/Example01-FactureStatut-ok.xml"
        );

        $actionResult = $this->triggerActionOnDocument($document['id_d'], self::ACTION_CREATE_STATUT_FACTURE_CPP);

        $this->assertFalse($actionResult);
        $this->assertLastMessage(
            "L'identifiant de structure 00000000169959 n'a pas été trouvé. L'identifiant CPP est invalide"
        );
        $this->assertLastDocumentAction('create-statut-facture-cpp-error', $document['id_d']);
    }

    /**
     * When the invoice cannot be found on chorus
     *
     * @test
     * @throws Exception
     */
    public function whenTheInvoiceIsNotFound()
    {
        $cppWrapper = $this->getMockBuilder(CPPWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapper->expects($this->any())
            ->method('getIdentifiantStructureCPPByIdentifiantStructure')
            ->willReturn(1234);
        $cppWrapper->expects($this->any())
            ->method('getCppInvoiceId')
            ->willThrowException(new Exception("Impossible de trouver la facture 10000"));

        $cppWrapperFactory = $this->getMockBuilder(CPPWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapperFactory->expects($this->any())->method('newInstance')->willReturn($cppWrapper);

        $this->getObjectInstancier()->setInstance(CPPWrapperFactory::class, $cppWrapperFactory);

        $this->createCppConnector(self::FLUX_STATUT_FACTURE_CPP);
        $document = $this->createDocument(self::FLUX_STATUT_FACTURE_CPP);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->addFileFromCopy(
            'fichier_statut_facture',
            'Example01-FactureStatut-ok.xml',
            __DIR__ . "/../fixtures/Example01-FactureStatut-ok.xml"
        );

        $actionResult = $this->triggerActionOnDocument($document['id_d'], self::ACTION_CREATE_STATUT_FACTURE_CPP);

        $this->assertFalse($actionResult);
        $this->assertLastMessage("Impossible de trouver la facture 10000");
        $this->assertLastDocumentAction('create-statut-facture-cpp-error', $document['id_d']);
    }

    /**
     * When creating a new status change request and everything happens correctly
     *
     * @test
     * @throws Exception
     */
    public function whenCreatingTheDocument()
    {
        $cppWrapper = $this->getMockBuilder(CPPWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapper->expects($this->any())
            ->method('getIdentifiantStructureCPPByIdentifiantStructure')
            ->willReturn(1234);
        $cppWrapper->expects($this->any())->method('getCppInvoiceId')->willReturn(10000);

        $cppWrapperFactory = $this->getMockBuilder(CPPWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapperFactory->expects($this->any())->method('newInstance')->willReturn($cppWrapper);

        $this->getObjectInstancier()->setInstance(CPPWrapperFactory::class, $cppWrapperFactory);

        $this->createCppConnector(self::FLUX_STATUT_FACTURE_CPP);
        $document = $this->createDocument(self::FLUX_STATUT_FACTURE_CPP);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->addFileFromCopy(
            'fichier_statut_facture',
            'Example01-FactureStatut-ok.xml',
            __DIR__ . "/../fixtures/Example01-FactureStatut-ok.xml"
        );

        $actionResult = $this->triggerActionOnDocument($document['id_d'], self::ACTION_CREATE_STATUT_FACTURE_CPP);

        $this->assertTrue($actionResult);
        $this->assertLastMessage("Importation du document Pastell");
    }

    /**
     * When the status requested is unknown
     *
     * @test
     * @throws Exception
     */
    public function whenTheStatusIsUnknown()
    {
        $cppWrapper = $this->getMockBuilder(CPPWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapper->expects($this->any())
            ->method('getIdentifiantStructureCPPByIdentifiantStructure')
            ->willReturn(1234);
        $cppWrapper->expects($this->any())->method('getCppInvoiceId')->willReturn(10000);

        $cppWrapperFactory = $this->getMockBuilder(CPPWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapperFactory->expects($this->any())->method('newInstance')->willReturn($cppWrapper);

        $this->getObjectInstancier()->setInstance(CPPWrapperFactory::class, $cppWrapperFactory);

        $this->createCppConnector(self::FLUX_STATUT_FACTURE_CPP);
        $document = $this->createDocument(self::FLUX_STATUT_FACTURE_CPP);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        // load file with a status id that doesn't exist (17)
        $donneesFormulaire->addFileFromCopy(
            'fichier_statut_facture',
            'Example02-FactureStatut-ko.xml',
            __DIR__ . "/../fixtures/Example02-FactureStatut-ko.xml"
        );

        $actionResult = $this->triggerActionOnDocument($document['id_d'], self::ACTION_CREATE_STATUT_FACTURE_CPP);

        $this->assertFalse($actionResult);
        $this->assertLastMessage("L'identifiant 17 est inconnu");
        $this->assertLastDocumentAction('create-statut-facture-cpp-error', $document['id_d']);
    }

    /**
     * When chorus sends more than one invoice with the same number for the same supplier (unlikely to happen)
     *
     * @test
     * @throws Exception
     */
    public function whenMultipleInvoicesAreFound()
    {
        $cppWrapper = $this->getMockBuilder(CPPWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapper->expects($this->any())
            ->method('getIdentifiantStructureCPPByIdentifiantStructure')
            ->willReturn(1234);
        $cppWrapper->expects($this->any())
            ->method('getCppInvoiceId')
            ->willThrowException(new Exception("Plusieurs factures ont été trouvé avec le numéro 10000"));

        $cppWrapperFactory = $this->getMockBuilder(CPPWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapperFactory->expects($this->any())->method('newInstance')->willReturn($cppWrapper);

        $this->getObjectInstancier()->setInstance(CPPWrapperFactory::class, $cppWrapperFactory);

        $this->createCppConnector(self::FLUX_STATUT_FACTURE_CPP);
        $document = $this->createDocument(self::FLUX_STATUT_FACTURE_CPP);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->addFileFromCopy(
            'fichier_statut_facture',
            'Example01-FactureStatut-ok.xml',
            __DIR__ . "/../fixtures/Example01-FactureStatut-ok.xml"
        );

        $actionResult = $this->triggerActionOnDocument($document['id_d'], self::ACTION_CREATE_STATUT_FACTURE_CPP);

        $this->assertFalse($actionResult);
        $this->assertLastMessage("Plusieurs factures ont été trouvé avec le numéro 10000");
        $this->assertLastDocumentAction('create-statut-facture-cpp-error', $document['id_d']);
    }

    /**
     * When the file is not validated against the schema
     *
     * @test
     * @throws Exception
     */
    public function whenSchemaIsNotValidated()
    {
        $this->createCppConnector(self::FLUX_STATUT_FACTURE_CPP);
        $document = $this->createDocument(self::FLUX_STATUT_FACTURE_CPP);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);

        // load file with a datetime that doesn't exist (month 15)
        $donneesFormulaire->addFileFromCopy(
            'fichier_statut_facture',
            'Example03-FactureStatut-ko.xml',
            __DIR__ . "/../fixtures/Example03-FactureStatut-ko.xml"
        );

        $actionResult = $this->triggerActionOnDocument($document['id_d'], self::ACTION_CREATE_STATUT_FACTURE_CPP);

        $this->assertFalse($actionResult);
        $this->assertLastMessage(
            "Le fichier CPPStatutPivot est incorrect:  [Erreur #1824] Element 'Horodatage': '2018-15-18T16:51:55.000+01:00' is not a valid value of the atomic type 'xs:dateTime'.\n\n"
        );
        $this->assertLastDocumentAction('create-statut-facture-cpp-error', $document['id_d']);
    }

    /**
     * When the file is validated against the schema but doesn't use the expected element
     *
     * @test
     * @throws Exception
     */
    public function whenSchemaIsValidatedWithoutTheExpectedElement()
    {
        $this->createCppConnector(self::FLUX_STATUT_FACTURE_CPP);
        $document = $this->createDocument(self::FLUX_STATUT_FACTURE_CPP);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);

        $donneesFormulaire->addFileFromCopy(
            'fichier_statut_facture',
            'Example04-FactureStatut-ko.xml',
            __DIR__ . "/../fixtures/Example04-FactureStatut-ko.xml"
        );

        $actionResult = $this->triggerActionOnDocument($document['id_d'], self::ACTION_CREATE_STATUT_FACTURE_CPP);

        $this->assertFalse($actionResult);
        $this->assertLastMessage(
            "Le fichier CPPStatutPivot est incorrect : Il ne présente pas l'élément CPPFactureStatuts"
        );
        $this->assertLastDocumentAction('create-statut-facture-cpp-error', $document['id_d']);
    }
}
