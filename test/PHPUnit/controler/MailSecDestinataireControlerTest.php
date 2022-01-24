<?php

class MailSecDestinataireControlerTest extends ControlerTestCase
{
    private const FLUX_MAILSEC_BIDIR = "mailsec-bidir";
    private const ACTION_MAILSEC_BIDIR_ENVOI_MAIL = "envoi-mail";

    private const FLUX_MAILSEC = "mailsec";
    private const ACTION_MAILSEC_ENVOI_MAIL = "envoi";

    /**
     * @param string $flux_name
     * @param string $action_envoi
     * @return array
     */
    private function createMailSec(string $flux_name, string $action_envoi): array
    {
        $this->createConnecteurForTypeDossier($flux_name, MailSec::CONNECTEUR_ID);

        $id_d = $this->createDocument($flux_name)['id_d'];
        $this->configureDocument($id_d, [
            'objet' => 'test de mail',
            'to' => "test@libriciel.fr",
            'message' => 'message de test'
        ]);
        $this->triggerActionOnDocument($id_d, $action_envoi);

        $info = $this->getObjectInstancier()->getInstance(DocumentEmail::class)->getInfo($id_d);
        $key = $info[0]['key'];

        return ['id_d' => $id_d,'key' => $key];
    }

    /**
     * @throws Exception
     */
    public function testIndexAction()
    {
        $mail_sec_info  = $this->createMailSec(self::FLUX_MAILSEC, self::ACTION_MAILSEC_ENVOI_MAIL);
        $key = $mail_sec_info['key'];
        $id_d = $mail_sec_info['id_d'];

        $mailsecController = $this->getControlerInstance(MailSecDestinataireControler::class);

        $this->setGetInfo(['key' => $key]);
        $mailsecController->setServerInfo(['REMOTE_ADDR' => '127.0.0.1']);

        ob_start();
        $mailsecController->indexAction();
        ob_end_clean();
        $view_parameter = $mailsecController->getViewParameter();
        $this->assertEquals($key, $view_parameter['mailSecInfo']->key);
        $this->assertEquals($id_d, $view_parameter['mailSecInfo']->id_d);
    }

    /**
     * @throws Exception
     */
    public function testRepondreAction()
    {

        $mail_sec_info  = $this->createMailSec(self::FLUX_MAILSEC_BIDIR, self::ACTION_MAILSEC_BIDIR_ENVOI_MAIL);
        $key = $mail_sec_info['key'];
        $id_d = $mail_sec_info['id_d'];

        /** @var MailSecDestinataireControler $mailsecController */
        $mailsecController = $this->getControlerInstance(MailSecDestinataireControler::class);

        $this->setGetInfo(['key' => $key]);
        $mailsecController->setServerInfo(['REMOTE_ADDR' => '127.0.0.1']);

        ob_start();
        $mailsecController->repondreAction();
        ob_end_clean();
        $view_parameter = $mailsecController->getViewParameter();
        /** @var MailSecInfo $mailSecInfo */
        $mailSecInfo = $view_parameter['mailSecInfo'];
        $this->assertEquals($key, $view_parameter['mailSecInfo']->key);
        $this->assertEquals($id_d, $view_parameter['mailSecInfo']->id_d);
        $this->assertEquals(self::FLUX_MAILSEC_BIDIR . '-reponse', $mailSecInfo->flux_reponse);
    }


    /**
     * @throws Exception
     */
    public function testReponseEditionAction()
    {
        $mail_sec_info  = $this->createMailSec(self::FLUX_MAILSEC_BIDIR, self::ACTION_MAILSEC_BIDIR_ENVOI_MAIL);
        $key = $mail_sec_info['key'];
        $id_d = $mail_sec_info['id_d'];

        /** @var MailSecDestinataireControler $mailsecController */
        $mailsecController = $this->getControlerInstance(MailSecDestinataireControler::class);
        $this->setPostInfo(['reponse' => 'ceci est ma réponse','key' => $key]);
        $mailsecController->setServerInfo(['REMOTE_ADDR' => '127.0.0.1','REQUEST_METHOD' => 'POST']);

        ob_start();
        $mailsecController->repondreAction();
        try {
            $mailsecController->reponseEditionAction();
        } catch (Exception $e) {
        }
        $mailsecController->validationAction();
        try {
            $this->setPostInfo(['key' => $key]);
            $mailsecController->doValidationAction();
        } catch (Exception $e) {
        }
        ob_end_clean();

        $documentEmail = $this->getObjectInstancier()->getInstance(DocumentEmail::class);
        $info = $documentEmail->getInfoFromKey($key);
        $id_de = $info['id_de'];

        $documentEmailReponseSQL = $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class);
        $this->assertEquals('', $documentEmailReponseSQL->getAllReponse($id_d)[$id_de]['titre']);

        $document = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertSame(
            '1',
            $document->get('sent_mail_read')
        );
        $this->assertSame(
            '1',
            $document->get('sent_mail_answered')
        );
    }

    /**
     * @throws Exception
     */
    public function testRecuperationFichierAction()
    {
        $mail_sec_info  = $this->createMailSec(self::FLUX_MAILSEC_BIDIR, self::ACTION_MAILSEC_BIDIR_ENVOI_MAIL);
        $key = $mail_sec_info['key'];
        $id_d = $mail_sec_info['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->addFileFromData('document_attache', 'foo.txt', 'bar');

        /** @var MailSecDestinataireControler $mailsecController */
        $mailsecController = $this->getControlerInstance(MailSecDestinataireControler::class);

        $this->setGetInfo(['key' => $key,'field' => 'document_attache']);
        $mailsecController->setServerInfo(['REMOTE_ADDR' => '127.0.0.1']);

        ob_start();
        $mailsecController->recuperationFichierAction();
        $output = ob_get_clean();
        $this->assertEquals('Content-type: text/plain
Content-disposition: attachment; filename="foo.txt"
Expires: 0
Cache-Control: must-revalidate, post-check=0,pre-check=0
Pragma: public
bar', $output);
    }

    /**
     * @throws Exception
     */
    public function testSuppressionFichierAction()
    {
        $mail_sec_info  = $this->createMailSec(self::FLUX_MAILSEC_BIDIR, self::ACTION_MAILSEC_BIDIR_ENVOI_MAIL);
        $key = $mail_sec_info['key'];

        /** @var MailSecDestinataireControler $mailsecController */
        $mailsecController = $this->getControlerInstance(MailSecDestinataireControler::class);
        $this->setPostInfo(['reponse' => 'ceci est ma réponse','key' => $key]);
        $mailsecController->setServerInfo(['REMOTE_ADDR' => '127.0.0.1','REQUEST_METHOD' => 'POST']);

        ob_start();
        $mailsecController->repondreAction();
        try {
            $mailsecController->reponseEditionAction();
        } catch (Exception $e) {
        }
        ob_end_clean();

        $documentEmail = $this->getObjectInstancier()->getInstance(DocumentEmail::class);
        $info = $documentEmail->getInfoFromKey($key);
        $id_de = $info['id_de'];

        $documentEmailReponseSQL = $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class);
        $id_d_reponse = $documentEmailReponseSQL->getInfo($id_de)['id_d_reponse'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d_reponse);
        $donneesFormulaire->addFileFromData('document_attache', 'foo.txt', 'bar');


        $this->setPostInfo(['key' => $key,'field' => 'document_attache','fichier_reponse' => 1]);
        ob_start();
        $mailsecController->recuperationFichierAction();

        $output = ob_get_clean();
        $this->assertEquals('Content-type: text/plain
Content-disposition: attachment; filename="foo.txt"
Expires: 0
Cache-Control: must-revalidate, post-check=0,pre-check=0
Pragma: public
bar', $output);

        try {
            $mailsecController->suppressionFichierAction();
        } catch (Exception $e) {
        }

        $this->expectExceptionMessage("Ce fichier n'existe pas");
        $mailsecController->recuperationFichierAction();
    }

    public function testPasswordAction()
    {
        $mail_sec_info  = $this->createMailSec(self::FLUX_MAILSEC_BIDIR, self::ACTION_MAILSEC_BIDIR_ENVOI_MAIL);
        $key = $mail_sec_info['key'];

        /** @var MailSecDestinataireControler $mailsecController */
        $mailsecController = $this->getControlerInstance(MailSecDestinataireControler::class);
        $this->setGetInfo(['key' => $key]);

        $this->expectOutputRegex("#<input type='password' name='password' />#");
        $mailsecController->passwordAction();
    }

    public function testInvalidAction()
    {
        $mail_sec_info  = $this->createMailSec(self::FLUX_MAILSEC_BIDIR, self::ACTION_MAILSEC_BIDIR_ENVOI_MAIL);
        $key = $mail_sec_info['key'];

        /** @var MailSecDestinataireControler $mailsecController */
        $mailsecController = $this->getControlerInstance(MailSecDestinataireControler::class);
        $this->setGetInfo(['key' => $key]);

        $this->expectOutputRegex("#La clé du message ne correspond à aucun mail sécurisé#");
        $mailsecController->invalidAction();
    }

    public function fluxActionProvider()
    {
        return [
            [self::FLUX_MAILSEC_BIDIR, self::ACTION_MAILSEC_BIDIR_ENVOI_MAIL],
            [self::FLUX_MAILSEC, self::ACTION_MAILSEC_ENVOI_MAIL]
        ];
    }

    /**
     * @dataProvider fluxActionProvider
     * @param string $flux_name
     * @param string $action_envoi
     */
    public function testSupprimerMailSecActionWhenReception(string $flux_name, string $action_envoi)
    {
        $mail_sec_info  = $this->createMailSec($flux_name, $action_envoi);
        $key = $mail_sec_info['key'];
        $id_d = $mail_sec_info['id_d'];

        $this->assertLastMessage("Le document a été envoyé au(x) destinataire(s)");
        $this->assertActionPossible(['renvoi', 'non-recu'], $id_d);

        $documentEmail = $this->getObjectInstancier()->getInstance(DocumentEmail::class);
        $document_email_info = $documentEmail->getInfo($id_d);
        $documentEmail->consulter($document_email_info[0]['key'], $this->getJournal());

        $this->assertLastDocumentAction('reception', $id_d);
        $this->assertActionPossible(['supression', 'renvoi'], $id_d);
    }

    /**
     * @dataProvider fluxActionProvider
     * @param string $flux_name
     * @param string $action_envoi
     */
    public function testSupprimerMailSecActionWhenNonRecu(string $flux_name, string $action_envoi)
    {
        $mail_sec_info  = $this->createMailSec($flux_name, $action_envoi);
        $id_d = $mail_sec_info['id_d'];

        $this->assertLastMessage("Le document a été envoyé au(x) destinataire(s)");
        $this->assertActionPossible(['renvoi', 'non-recu'], $id_d);

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "non-recu")
        );
        $this->assertLastMessage("L'action Non reçu a été executée sur le document");

        $this->assertLastDocumentAction('non-recu', $id_d);
        $this->assertActionPossible(['supression'], $id_d);
    }
}
