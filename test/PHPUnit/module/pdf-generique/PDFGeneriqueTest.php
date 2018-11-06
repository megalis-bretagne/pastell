<?php


class PDFGeneriqueTest extends PastellTestCase
{

    const FILENAME = "Délib Libriciel.pdf";
    const ANNEXE1 = "Annexe1 Délib.pdf";
    const ANNEXE2 = "Annexe2 Délib.pdf";
    const SIGNATURE_ENVOIE ="send-iparapheur";

    private $id_d;

    /**
     * @throws Exception
     */
    protected function setUp() {
        parent::setUp();

        $this->createConnecteurSignature('pdf-generique');

        $result= $this->getInternalAPI()->post(
            "/Document/".PastellTestCase::ID_E_COL,array('type'=>'pdf-generique')
        );
        $this->id_d = $result['id_d'];
    }

    private function renseigneDoc() {

        $this->getInternalAPI()->patch(
            "/entite/1/document/$this->id_d",
            array('libelle'=>'Test pdf générique',
                'envoi_signature'=>'1',
                'iparapheur_type'=>'Actes',
                'iparapheur_sous_type'=>'Délibération',
            )
        );

    }

    /**
     * @throws Exception
     */
    private function postFichiers() {

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $donneesFormulaire->addFileFromCopy('document',self::FILENAME,__DIR__."/fixtures/".self::FILENAME,0);
        $donneesFormulaire->addFileFromCopy('annexe',self::ANNEXE1,__DIR__."/fixtures/".self::ANNEXE1,0);
        $donneesFormulaire->addFileFromCopy('annexe',self::ANNEXE2,__DIR__."/fixtures/".self::ANNEXE2,1);
    }

    /**
     * @throws Exception
     */
    public function testSignatureEnvoieOK(){

        $this->renseigneDoc();
        $this->postFichiers();

        $actionExecutorFactory = $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class);
        $actionExecutorFactory->executeOnDocument(1,0,$this->id_d,self::SIGNATURE_ENVOIE);

        $this->assertEquals(
            "Le document a été envoyé au parapheur électronique",
            $this->getObjectInstancier()->getInstance('ActionExecutorFactory')->getLastMessage()
        );

    }

    /**
     * @param $flux_id
     */
    protected function createConnecteurSignature($flux_id){

        $result = $this->getInternalAPI()->post(
            "/entite/".self::ID_E_COL."/connecteur",
            array('libelle'=> 'Signature' , 'id_connecteur'=>'fakeIparapheur')
        );

        $id_ce = $result['id_ce'];

        $this->getInternalAPI()->post(
            "/entite/".self::ID_E_COL."/flux/$flux_id/connecteur/$id_ce",
            array('type' => 'signature')
        );
    }
}