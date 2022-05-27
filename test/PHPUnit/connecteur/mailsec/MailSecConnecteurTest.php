<?php

class MailSecConnecteurTest extends PastellTestCase
{
    use MailerTransportTestingTrait;

    public const FLUX_ID =  'mailsec';
    private const EMAIL = 'foo@test.com';
    private const DESTINATAIRE = 'destinataire';
    private const CONTENU = 'contenu';
    private const ENTETE = 'entete';

    /** @var DonneesFormulaire */
    private $connecteurConfig;

    private $contentHTML;
    private $embededImage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setMailerTransportForTesting();
        $this->contentHTML = '';
        $this->embededImage = [];
    }

    /**
     * @return DocumentEmail
     */
    private function getDocumentEmail(): DocumentEmail
    {
        return $this->getObjectInstancier()->getInstance(DocumentEmail::class);
    }

    /**
     * @throws DonneesFormulaireException
     * @throws Exception
     */
    private function getMailSec(): MailSec
    {
        $result = $this->createConnector('mailsec', "Connecteur mailsec de test");
        $id_ce  = $result['id_ce'];
        $this->configureConnector(
            $id_ce,
            ['mailsec_subject' => 'entite: %ENTITE% -- titre : %TITRE%']
        );
        $this->connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
        if ($this->contentHTML) {
            $this->connecteurConfig->addFileFromCopy(
                'content_html',
                'content.html',
                $this->contentHTML,
            );
        }
        foreach ($this->embededImage as $filenum => $filename) {
            $this->connecteurConfig->addFileFromCopy(
                'embeded_image',
                $filename,
                __DIR__ . "/fixtures/image-exemple.png",
                $filenum
            );
        }

        $id_d = $this->createDocument('test')['id_d'];
        $this->configureDocument(
            $id_d,
            ['raison_sociale' => 'Libriciel SCOP', 'numero_facture' => 'FOO42', 'toto' => 'mon titre']
        );
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->addFileFromCopy(
            'metadata',
            "metadata.json",
            __DIR__ . "/fixtures/mail-metadata.json"
        );
        /** @var MailSec $mailsec */
        $mailsec = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $mailsec->setDocDonneesFormulaire($donneesFormulaire);
        return $mailsec;
    }

    /**
     * @param $filepath
     * @throws DonneesFormulaireException
     */
    private function addContentHTML($filepath)
    {
        $this->contentHTML = $filepath;
    }

    /**
     * @param string $filename
     * @param int $filenum
     * @throws DonneesFormulaireException
     */
    private function addEmbededImage(string $filename = 'image.png', int $filenum = 0)
    {
        $this->embededImage[$filenum] = $filename;
    }

    /**
     * @throws DonneesFormulaireException
     * @throws Exception
     */
    public function testTest()
    {
        $this->getMailSec()->test();
        $this->assertMessageContainsString('Subject: entite: Bourg-en-Bresse -- titre : mon titre');
    }

    /**
     * @throws Exception
     */
    public function testSendOneMail()
    {
        $key = $this->getDocumentEmail()->add(1, self::EMAIL, "to");
        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $this->getMailSec()->sendOneMail(1, 1, $document_email_info[DocumentEmail::ID_DE]);
        $this->assertMailEqualsFile(__DIR__ . "/fixtures/mail-text-output.txt");
    }

    private function assertMailEqualsFile(string $filename): void
    {
        $mailAsString = $this->getMailerTransport()->getSentMessage()->toString();
        $this->assertMailContentEqualsFile($filename, $mailAsString);
    }
    /**
     * @throws Exception
     */
    public function testSendAllMail()
    {
        $this->getDocumentEmail()->add(1, self::EMAIL, "to");
        $this->getMailSec()->sendAllMail(1, 1);
        $this->assertMailEqualsFile(__DIR__ . "/fixtures/mail-text-output.txt");
    }

    /**
     * @throws Exception
     */
    public function testSendHTML()
    {
        $this->addContentHTML(__DIR__ . "/fixtures/mail-exemple.html");
        $this->addEmbededImage('image1.png');
        $this->addEmbededImage('image2.png', 1);

        $mailsec = $this->getMailSec();


        $key = $this->getDocumentEmail()->add(1, self::EMAIL, "to");

        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $mailsec->sendOneMail(1, 1, $document_email_info[DocumentEmail::ID_DE]);
        $this->assertMailEqualsFile(__DIR__ . "/fixtures/mail-html-output.txt");
    }

    /**
     * @throws Exception
     */
    public function testSendHTMLFluxKeyNotFound()
    {
        $this->addContentHTML(__DIR__ . "/fixtures/mail-exemple-key-not-found.html");
        $this->addEmbededImage();

        $mailsec = $this->getMailSec();

        $key = $this->getDocumentEmail()->add(1, self::EMAIL, "to");

        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("La clé foo de @metadata:facturx:data:foo n'existe pas, vérifier la syntaxe.");
        $mailsec->sendOneMail(1, 1, $document_email_info[DocumentEmail::ID_DE]);
    }

    /**
     * @throws Exception
     */
    public function testSendHTMLFluxMetadataFileNotFound()
    {
        $this->addContentHTML(__DIR__ . "/fixtures/mail-exemple-metadata-file-not-found.html");
        $this->addEmbededImage();

        $mailsec = $this->getMailSec();

        $key = $this->getDocumentEmail()->add(1, self::EMAIL, "to");

        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Erreur de lecture du contenu de metadata_not_found");
        $mailsec->sendOneMail(1, 1, $document_email_info[DocumentEmail::ID_DE]);
    }

    /**
     * @throws Exception
     */
    public function testSendHTMLFluxKeyBadType()
    {
        $this->addContentHTML(__DIR__ . "/fixtures/mail-exemple-key-bad-type.html");

        $this->addEmbededImage();

        $mailsec = $this->getMailSec();

        $key = $this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");

        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "La valeur de @metadata:facturx:data n'est pas un type simple, vérifier la syntaxe."
        );
        $mailsec->sendOneMail(1, 1, $document_email_info['id_de']);
    }

    /**
     * @throws Exception
     */
    public function testSendLinkTest()
    {
        $mailsec = $this->getMailSec();

        $this->connecteurConfig->setData(
            'mailsec_content',
            "Un lien ici : %LINK%. C'était mon lien"
        );
        $key = $this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");
        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $mailsec->sendOneMail(1, 1, $document_email_info['id_de']);

        $this->assertMailEqualsFile(__DIR__ . "/fixtures/mail-link.txt");
    }

    /**
     * @throws Exception
     */
    public function testSendAllMailWithMultiplePeople()
    {
        $this->addContentHTML(__DIR__ . "/fixtures/mail-exemple.html");
        $mailsec = $this->getMailSec();

        $this->getDocumentEmail()->add(1, "jdoe@example.org", "to");
        $this->getDocumentEmail()->add(1, "john.doe@example.org", "to");

        $mailsec->sendAllMail(1, 1);

        $this->assertMailContentEqualsFile(
            __DIR__ . "/fixtures/mail_jdoe.txt",
            $this->getMailerTransport()->getAllSentMessages()[0]->toString(),
        );
        $this->assertMailContentEqualsFile(
            __DIR__ . "/fixtures/mail_john.doe.txt",
            $this->getMailerTransport()->getAllSentMessages()[1]->toString(),
        );
    }

    private function assertMailContentEqualsFile(string $filename, string $mailAsString): void
    {
        $mailAsString = preg_replace(
            "#X-PASTELL-DOCUMENT: .*#",
            "X-PASTELL-DOCUMENT: NOTTESTABLE\r",
            $mailAsString
        );
        $mailAsString = preg_replace(
            "#Date: .*#",
            "DATE: NOTTESTABLE\r",
            $mailAsString
        );
        $mailAsString = preg_replace(
            "#Message-ID: .*#",
            "Message-ID: NOTTESTABLE\r",
            $mailAsString
        );
        $mailAsString = preg_replace(
            "#index\.php\?key=.*#",
            "index.php?key=NOTTESTABLE",
            $mailAsString
        );
        $mailAsString = preg_replace(
            "#boundary=.*#",
            "boundary=NOTTESTABLE\r",
            $mailAsString
        );
        $mailAsString = preg_replace(
            "#--[^ \n]+#",
            "--NOTTESTABLE\r",
            $mailAsString
        );
        $mailAsString = preg_replace(
            "#cid:[0-9a-f]*#",
            "cid:NOTTESTABLE",
            $mailAsString
        );
        $mailAsString = preg_replace(
            "#Content-ID: <[0-9a-f]*#",
            "Content-ID: <NOTTESTABLE",
            $mailAsString
        );

        //file_put_contents($filename, $mailAsString);
        self::assertStringEqualsFile(
            $filename,
            $mailAsString
        );
    }
}
