<?php

require_once(__DIR__ . '/../../../../connecteur/mailsec/MailSec.class.php');


class MailSecConnecteurTest extends PastellTestCase
{
    public const FLUX_ID =  'mailsec';
    private const EMAIL = 'foo@test.com';
    private const DESTINATAIRE = 'destinataire';
    private const CONTENU = 'contenu';
    private const ENTETE = 'entete';

    /** @var DonneesFormulaire */
    private $connecteurConfig;

    /**
     * @return DocumentEmail
     */
    private function getDocumentEmail()
    {
        return $this->getObjectInstancier()->getInstance(DocumentEmail::class);
    }

    /**
     * @return ZenMail
     */
    private function getZenMail()
    {
        return $this->getObjectInstancier()->getInstance(ZenMail::class);
    }

    /**
     * @param ZenMail $zenMail
     * @return MailSec
     * @throws DonneesFormulaireException
     */
    public function getMailSec(ZenMail $zenMail)
    {
        $mailsec = new MailSec(
            $zenMail,
            $this->getDocumentEmail(),
            $this->getJournal(),
            $this->getObjectInstancier()->{'EntiteSQL'},
            $this->getConnecteurFactory()
        );

        $result = $this->getInternalAPI()->post(
            "/entite/1/connecteur",
            array('libelle' => 'Connecteur mailsec de test','id_connecteur' => 'mailsec')
        );

        $id_ce  = $result['id_ce'];

        $this->connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
        $this->connecteurConfig->setData('mailsec_subject', 'entite: %ENTITE% -- titre : %TITRE%');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $donneesFormulaire->setData('raison_sociale', 'Libriciel SCOP');
        $donneesFormulaire->setData('numero_facture', 'FOO42');

        $donneesFormulaire->addFileFromCopy(
            'metadata',
            "metadata.json",
            __DIR__ . "/fixtures/mail-metadata.json"
        );

        $mailsec->setDocDonneesFormulaire($donneesFormulaire);

        $mailsec->setConnecteurConfig($this->connecteurConfig);

        return $mailsec;
    }

    /**
     * @throws Exception
     */
    public function testSendAllMail()
    {
        $zenMail = $this->getZenMail();
        $this->getDocumentEmail()->add(1, self::EMAIL, "to");

        $this->getMailSec($zenMail)->sendAllMail(1, 1);
        $all_info = $zenMail->getAllInfo();
        $this->assertCount(1, $all_info);
        $this->assertEquals(self::EMAIL, $all_info[0][self::DESTINATAIRE]);
    }

    /**
     * @throws Exception
     */
    public function testSendOneMail()
    {
        $zenMail = $this->getZenMail();


        $key = $this->getDocumentEmail()->add(1, self::EMAIL, "to");
        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $this->getMailSec($zenMail)->sendOneMail(1, 1, $document_email_info[DocumentEmail::ID_DE]);

        $all_info = $zenMail->getAllInfo();
        $this->assertCount(1, $all_info);
        $this->assertEquals(self::EMAIL, $all_info[0][self::DESTINATAIRE]);

        $this->assertEquals('=?UTF-8?Q?entite:=20=20--=20titre=20:=20?=', $all_info[0]['sujet']);

        $info = $this->getDocumentEmail()->getInfoFromPK($document_email_info[DocumentEmail::ID_DE]);
        $this->assertEquals(1, $info['nb_renvoi']);
    }

    /**
     * @throws DonneesFormulaireException
     */
    public function testTest()
    {
        $zenMail = $this->getZenMail();
        $this->getMailSec($zenMail)->test();
        $all_info = $zenMail->getAllInfo();
        $this->assertEquals('ne-pas-repondre@libriciel.coop', $all_info[0][self::DESTINATAIRE]);
    }

    /**
     * @throws DonneesFormulaireException
     */
    public function testEmetteur()
    {
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);
        $this->connecteurConfig->setData('mailsec_from_description', 'ma_collectivite');
        $this->connecteurConfig->setData('mailsec_from', 'mail_collectivite@example.org');
        $this->connecteurConfig->setData('mailsec_reply_to', 'mail_reply_to@example.org');
        $mailsec->test();
        $info_entete = $zenMail->getAllInfo()[0][self::ENTETE];

        if (MODE_MUTUALISE) {
            $this->assertEquals(
                'From: =?utf-8?B?bWFfY29sbGVjdGl2aXRl?=<' . PLATEFORME_MAIL . '>
Reply-To: mail_reply_to@example.org
Content-Type: text/plain; charset="UTF-8"',
                $info_entete
            );
        } else {
            $this->assertEquals(
                'From: =?utf-8?B?bWFfY29sbGVjdGl2aXRl?=<mail_collectivite@example.org>
Reply-To: mail_reply_to@example.org
Content-Type: text/plain; charset="UTF-8"',
                $info_entete
            );
        }
    }

    /**
     * @throws Exception
     */
    public function testSendHTML()
    {
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);


        $this->addContentHTML(__DIR__ . "/fixtures/mail-exemple.html");
        $this->addEmbededImage('image1.png', 0);
        $this->addEmbededImage('image2.png', 1);

        $key = $this->getDocumentEmail()->add(1, self::EMAIL, "to");

        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $mailsec->sendOneMail(1, 1, $document_email_info[DocumentEmail::ID_DE]);
        $all_info = $zenMail->getAllInfo();
        $this->assertRegExp("#Content-Type: text/html;#", $all_info[0][self::CONTENU]);
        $this->assertRegExp("#Content-ID: <image0>#", $all_info[0][self::CONTENU]);
        $this->assertRegExp("#Content-ID: <image1>#", $all_info[0][self::CONTENU]);
        $this->assertRegExp("#Content-Disposition: inline, filename=\"image1.png#", $all_info[0][self::CONTENU]);
        $this->assertRegExp("#Content-Disposition: inline, filename=\"image2.png#", $all_info[0][self::CONTENU]);
        $this->assertRegExp("#FOO42#", $all_info[0][self::CONTENU]);
        $this->assertRegExp("#Le montant de cette commande est de : 42 franc#", $all_info[0][self::CONTENU]);
    }

    /**
     * @param $filepath
     * @throws DonneesFormulaireException
     */
    private function addContentHTML($filepath)
    {
        $this->connecteurConfig->addFileFromCopy(
            'content_html',
            'content.html',
            $filepath
        );
    }

    /**
     * @param string $filename
     * @param int $filenum
     * @throws DonneesFormulaireException
     */
    private function addEmbededImage($filename = 'image.png', $filenum = 0)
    {
        $this->connecteurConfig->addFileFromCopy(
            'embeded_image',
            $filename,
            __DIR__ . "/fixtures/image-exemple.png",
            $filenum
        );
    }

    /**
     * @throws Exception
     */
    public function testSendHTMLFluxKeyNotFound()
    {
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);

        $this->addContentHTML(__DIR__ . "/fixtures/mail-exemple-key-not-found.html");
        $this->addEmbededImage();

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
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);

        $this->addContentHTML(__DIR__ . "/fixtures/mail-exemple-metadata-file-not-found.html");
        $this->addEmbededImage();

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
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);

        $this->addContentHTML(__DIR__ . "/fixtures/mail-exemple-key-bad-type.html");

        $this->addEmbededImage();
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
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);

        $this->connecteurConfig->setData(
            'mailsec_content',
            "Un lien ici : %LINK%. C'était mon lien"
        );
        $key = $this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");
        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $mailsec->sendOneMail(1, 1, $document_email_info['id_de']);
        $all_info = $zenMail->getAllInfo();
        $this->assertRegExp(
            "#^Un lien ici : .*index.php\?key=.*. C'était mon lien$#",
            $all_info[0][self::CONTENU]
        );
    }

    /**
     * @throws Exception
     */
    public function testSendAllMailWithMultiplePeople()
    {
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);

        $this->addContentHTML(__DIR__ . "/fixtures/mail-exemple-only-link.html");

        $key1 = $this->getDocumentEmail()->add(1, "jdoe@example.org", "to");
        $key2 = $this->getDocumentEmail()->add(1, "john.doe@example.org", "to");

        $mailsec->sendAllMail(1, 1);
        $all_info = $zenMail->getAllInfo();

        $this->assertContains($key1, $all_info[0][self::CONTENU]);
        $this->assertContains($key2, $all_info[1][self::CONTENU]);
        $this->assertNotContains($key1, $all_info[1][self::CONTENU]);
    }
}
