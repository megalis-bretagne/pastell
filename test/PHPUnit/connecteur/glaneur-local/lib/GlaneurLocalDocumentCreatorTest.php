<?php
require_once __DIR__."/../../../../../connecteur/glaneur-local/lib/GlaneurLocalDocumentCreator.class.php";

class GlaneurLocalDocumentCreatorTest extends PastellTestCase {


    /**
     * @throws Exception
     */
    public function testCreateDocument(){
        $glaneurLocalDocumentCreator = $this->getObjectInstancier()->getInstance('GlaneurLocalDocumentCreator');

        $glaneurLocalDocumentInfo = new GlaneurLocalDocumentInfo(1);
        $glaneurLocalDocumentInfo->nom_flux = 'helios-automatique';
        $glaneurLocalDocumentInfo->metadata = ['objet' => 'test_pes'];
        $glaneurLocalDocumentInfo->element_files_association = [
            'fichier_pes' => ['test.xml']
        ];
        $glaneurLocalDocumentInfo->action_ok = 'importation';
        $glaneurLocalDocumentInfo->action_ko = 'erreur_import';


        $this->assertNotEmpty(
            $glaneurLocalDocumentCreator->create(
                $glaneurLocalDocumentInfo,
               __DIR__."/../fixtures/pes_exemple/"
        ));
    }

    /**
     * @throws Exception
     */
    public function testCreateDocumentFailed(){
        $glaneurLocalDocumentCreator = $this->getObjectInstancier()->getInstance('GlaneurLocalDocumentCreator');

        $glaneurLocalDocumentInfo = new GlaneurLocalDocumentInfo(1);
        $glaneurLocalDocumentInfo->nom_flux = 'helios-automatique';
        $glaneurLocalDocumentInfo->metadata = [];
        $glaneurLocalDocumentInfo->element_files_association = [
            'fichier_pes' => ['test.xml']
        ];
        $glaneurLocalDocumentInfo->action_ok = 'importation';
        $glaneurLocalDocumentInfo->action_ko = 'erreur_import';


        //$this->expectExceptionMessage("Le formulaire est incomplet : le champ «Objet» est obligatoire.");
        $this->assertNotEmpty(
        	$glaneurLocalDocumentCreator->create(
            	$glaneurLocalDocumentInfo,
            	__DIR__."/../fixtures/pes_exemple/"
        	)
		);
    }

}