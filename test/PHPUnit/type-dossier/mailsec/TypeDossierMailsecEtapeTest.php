<?php

require_once __DIR__ . "/../../pastell-core/type-dossier/TypeDossierLoader.class.php";


class TypeDossierMailsecEtapeTest extends PastellTestCase
{

    public const MAILSEC_ONLY = 'mailsec-only';

    /** @var TypeDossierLoader */
    private $typeDossierLoader;

    /**
     * @throws Exception
     */
    public function setUp()
    {
        parent::setUp();
        $this->typeDossierLoader = $this->getObjectInstancier()->getInstance(TypeDossierLoader::class);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->typeDossierLoader->unload();
    }

    /**
     * @throws Exception
     */
    public function testDepot()
    {
        $this->typeDossierLoader->createTypeDossierDefinitionFile(self::MAILSEC_ONLY);

        $info_connecteur = $this->createConnector("mailsec", "Mail sécurisé");
        $this->associateFluxWithConnector($info_connecteur['id_ce'], self::MAILSEC_ONLY, "mailsec");

        $info_connecteur = $this->createConnector("pdf-relance", "PDF Relance");
        $this->associateFluxWithConnector($info_connecteur['id_ce'], self::MAILSEC_ONLY, "pdf-relance");


        $info = $this->createDocument(self::MAILSEC_ONLY);
        $id_d = $info['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData(['titre' => 'Foo','to' => 'foo@bar.com']);
        $donneesFormulaire->addFileFromData('document', 'fichier1.txt', 'bar');

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "orientation")
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "send-mailsec")
        );
        $this->assertLastMessage("Le document a été envoyé au(x) destinataire(s)");


        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "mailsec-relance")
        );
        $last_message = $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class)->getLastMessage();
        $this->assertRegExp("#Relance programmée le#", $last_message);

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "renvoi")
        );
        $this->assertLastMessage("Un email a été renvoyé à tous les destinataires");


        $documentEmail = $this->getObjectInstancier()->getInstance(DocumentEmail::class);
        $document_email_info = $documentEmail->getInfo($id_d);
        $documentEmail->consulter($document_email_info[0]['key'], $this->getJournal());
        $this->assertLastDocumentAction('reception', $id_d);

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "orientation")
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");
        $this->assertLastDocumentAction('termine', $id_d);
    }
}
