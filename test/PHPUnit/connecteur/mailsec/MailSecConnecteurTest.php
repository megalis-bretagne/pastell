<?php

require_once(__DIR__ . '/../../../../connecteur/mailsec/MailSec.class.php');


class MailSecConnecteurTest extends PastellTestCase
{

    public const FLUX_ID =  'mailsec';

    /** @var DonneesFormulaire */
    private $connecteurConfig;

    /**
     * @return DocumentEmail
     */
    private function getDocumentEmail()
    {
        return $this->getObjectInstancier()->{'DocumentEmail'};
    }

    /**
     * @return ZenMail
     */
    private function getZenMail()
    {
        $zenMail = new ZenMail(new FileContentType());
        $zenMail->disableMailSending();
        return $zenMail;
    }

    /**
     * @param ZenMail $zenMail
     * @return MailSec
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

        $donneesFormulaire->addFileFromCopy('metadata', "metadata.json", __DIR__ . "/fixtures/mail-metadata.json");

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
        $email = "eric.pommateau@adullact-projet.com";
        $this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");

        $this->getMailSec($zenMail)->sendAllMail(1, 1);
        $all_info = $zenMail->getAllInfo();
        $this->assertCount(1, $all_info);
        $this->assertEquals($email, $all_info[0]['destinataire']);
    }

    /**
     * @throws Exception
     */
    public function testSendOneMail()
    {
        $zenMail = $this->getZenMail();

        $email = "eric.pommateau@adullact-projet.com";
        $key = $this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");
        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $this->getMailSec($zenMail)->sendOneMail(1, 1, $document_email_info['id_de']);

        $all_info = $zenMail->getAllInfo();
        $this->assertCount(1, $all_info);
        $this->assertEquals($email, $all_info[0]['destinataire']);

        $this->assertEquals('=?UTF-8?Q?entite:=20=20--=20titre=20:=20?=', $all_info[0]['sujet']);

        $info = $this->getDocumentEmail()->getInfoFromPK($document_email_info['id_de']);
        $this->assertEquals(1, $info['nb_renvoi']);
    }

    public function testTest()
    {
        $zenMail = $this->getZenMail();
        $this->getMailSec($zenMail)->test();
        $all_info = $zenMail->getAllInfo();
        $this->assertEquals('pastell@sigmalis.com', $all_info[0]['destinataire']);
    }

    /**
     * @throws Exception
     */
    public function testSendHTML()
    {
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);

        $this->connecteurConfig->addFileFromCopy('content_html', 'content.html', __DIR__ . "/fixtures/mail-exemple.html");
        $this->connecteurConfig->addFileFromCopy('embeded_image', 'image1.png', __DIR__ . "/fixtures/image-exemple.png", 0);
        $this->connecteurConfig->addFileFromCopy('embeded_image', 'image2.png', __DIR__ . "/fixtures/image-exemple.png", 1);


        $key = $this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");

        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $mailsec->sendOneMail(1, 1, $document_email_info['id_de']);
        $all_info = $zenMail->getAllInfo();
        $this->assertRegExp("#Content-Type: text/html;#", $all_info[0]['contenu']);
        $this->assertRegExp("#Content-ID: <image0>#", $all_info[0]['contenu']);
        $this->assertRegExp("#Content-ID: <image1>#", $all_info[0]['contenu']);
        $this->assertRegExp("#Content-Disposition: inline, filename=\"image1.png#", $all_info[0]['contenu']);
        $this->assertRegExp("#Content-Disposition: inline, filename=\"image2.png#", $all_info[0]['contenu']);
        $this->assertRegExp("#FOO42#", $all_info[0]['contenu']);
        $this->assertRegExp("#Le montant de cette commande est de : 42 franc#", $all_info[0]['contenu']);
    }


    /**
     * @throws Exception
     */
    public function testSendHTMLFluxKeyNotFound()
    {
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);

        $this->connecteurConfig->addFileFromCopy('content_html', 'content.html', __DIR__ . "/fixtures/mail-exemple-key-not-found.html");
        $this->connecteurConfig->addFileFromCopy('embeded_image', 'image.png', __DIR__ . "/fixtures/image-exemple.png");

        $key = $this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");

        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("La clé foo de @metadata:facturx:data:foo n'existe pas, vérifier la syntaxe.");
        $mailsec->sendOneMail(1, 1, $document_email_info['id_de']);
    }


    /**
     * @throws Exception
     */
    public function testSendHTMLFluxMetadataFileNotFound()
    {
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);

        $this->connecteurConfig->addFileFromCopy('content_html', 'content.html', __DIR__ . "/fixtures/mail-exemple-metadata-file-not-found.html");
        $this->connecteurConfig->addFileFromCopy('embeded_image', 'image.png', __DIR__ . "/fixtures/image-exemple.png");

        $key = $this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");

        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Erreur de lecture du contenu de metadata_not_found");
        $mailsec->sendOneMail(1, 1, $document_email_info['id_de']);
    }

    /**
     * @throws Exception
     */
    public function testSendHTMLFluxKeyBadType()
    {
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);

        $this->connecteurConfig->addFileFromCopy('content_html', 'content.html', __DIR__ . "/fixtures/mail-exemple-key-bad-type.html");
        $this->connecteurConfig->addFileFromCopy('embeded_image', 'image.png', __DIR__ . "/fixtures/image-exemple.png");

        $key = $this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");

        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("La valeur de @metadata:facturx:data n'est pas un type simple, vérifier la syntaxe.");
        $mailsec->sendOneMail(1, 1, $document_email_info['id_de']);
    }


    /**
     * @throws Exception
     */
    public function testSendLinkTest()
    {
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);

        $this->connecteurConfig->setData('mailsec_content', "Un lien ici : %LINK%. C'était mon lien");
        $key = $this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");
        $document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);

        $mailsec->sendOneMail(1, 1, $document_email_info['id_de']);
        $all_info = $zenMail->getAllInfo();
        $this->assertRegExp(
            "#^Un lien ici : .*index.php\?key=.*. C'était mon lien$#",
            $all_info[0]['contenu']
        );
    }

    /**
     * @throws Exception
     */
    public function testSendAllMailWithMultiplePeople()
    {
        $zenMail = $this->getZenMail();
        $mailsec = $this->getMailSec($zenMail);
        $this->connecteurConfig->addFileFromCopy('content_html', 'content.html', __DIR__ . "/fixtures/mail-exemple-only-link.html");

        $key1 = $this->getDocumentEmail()->add(1, "jdoe@example.org", "to");
        $key2 = $this->getDocumentEmail()->add(1, "john.doe@example.org", "to");

        $mailsec->sendAllMail(1, 1);
        $all_info = $zenMail->getAllInfo();

        $this->assertContains($key1, $all_info[0]['contenu']);
        $this->assertContains($key2, $all_info[1]['contenu']);
        $this->assertNotContains($key1, $all_info[1]['contenu']);
    }
}
