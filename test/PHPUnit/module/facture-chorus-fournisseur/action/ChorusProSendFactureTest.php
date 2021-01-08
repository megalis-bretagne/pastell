<?php

require_once __DIR__ . "/../../../../connecteur-type/PortailFactureConnecteur.class.php";
require_once __DIR__ . "/../../../../connecteur/cpp/CPP.class.php";

class ChorusProSendFactureTest extends ExtensionCppTestCase
{

    /**
     * @test
     * @throws Exception
     */
    public function ChorusProSendFacture()
    {

        $soumettreFacure_result = array (
            'codeRetour' => 0,
            'libelle' => 'GCU_MSG_01_000',
            'dateDepot' => '2019-03-27',
            'identifiantFactureCPP' => 3123988,
            'identifiantStructure' => '00000000000727',
            'numeroFacture' => 'FAC20190327',
            'statutFacture' => 'DEPOSEE'
        );

        $cppWrapper = $this->getMockBuilder(CPPWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cppWrapper->expects($this->any())->method('call')->willReturn($soumettreFacure_result);

        $cppWrapperFactory = $this->getMockBuilder(CPPWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapperFactory->expects($this->any())->method('newInstance')->willReturn($cppWrapper);

        $this->getObjectInstancier()->setInstance(CPPWrapperFactory::class, $cppWrapperFactory);


        $this->createCppConnector("facture-chorus-fournisseur");
        $document = $this->createDocument("facture-chorus-fournisseur");

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);

        $donneesFormulaire->addFileFromCopy('fichier_facture_pdf', 'Facture-PDF.pdf', __DIR__ . "/../../../fixtures/Facture-PDF.pdf");

        $actionResult = $this->triggerActionOnDocument($document['id_d'], 'send-chorus');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);

        $this->assertTrue($actionResult);
        $this->assertEquals('Facture-PDF.pdf', $donneesFormulaire->getFileName('fichier_original', 0));
        $this->assertLastMessage(
            "La facture FAC20190327 a été déposée sur Chorus Pro " .
            "avec l'identifiant 3123988"
        );
    }
}
