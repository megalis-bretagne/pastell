<?php

class MailSecDestinataireControler extends PastellControler
{
    private function getDocumentEmail()
    {
        return $this->getObjectInstancier()->getInstance(DocumentEmail::class);
    }

    private function redirectWebMailsec($to)
    {
        $this->absoluteRedirect(WEBSEC_BASE . "/" . $to);
    }

    private function validatePassword(DonneesFormulaire $donneesFormulaire, $key)
    {
        $ip = $this->getServerInfo('REMOTE_ADDR');
        if ($donneesFormulaire->get('password') && (empty($_SESSION["consult_ok_{$key}_{$ip}"]))) {
            $this->redirectWebMailsec("password.php?key={$key}");
        }
    }

    private function getFluxDestinataire($flux_mailsec)
    {
        $flux_destinataire = "{$flux_mailsec}-destinataire";

        if (! $this->getDocumentTypeFactory()->isTypePresent($flux_destinataire)) {
            $flux_destinataire = 'mailsec-destinataire';
        }
        return $flux_destinataire;
    }

    private function getFluxReponse($flux_mailsec, $type_destinataire)
    {
        $flux_reponse = "{$flux_mailsec}-reponse";

        if (($this->getDocumentTypeFactory()->isTypePresent($flux_reponse)) && ($type_destinataire == "to")) {
            return $flux_reponse;
        }
        return false;
    }

    /**
     * @return MailSecInfo
     * @throws Exception
     */
    private function getMailsecInfo()
    {
        $mailSecInfo = new MailSecInfo();
        $mailSecInfo->key = $this->getPostOrGetInfo()->get('key');

        $mailsec_info  = $this->getDocumentEmail()->getInfoFromKey($mailSecInfo->key);
        if ((! $mailsec_info)  || $mailsec_info['non_recu']) {
            $this->redirectWebMailsec("invalid.php");
        }

        $mailSecInfo->id_de = $mailsec_info['id_de'];
        $mailSecInfo->id_d = $mailsec_info['id_d'];
        $mailSecInfo->type_destinataire = $mailsec_info['type_destinataire'];

        $mailSecInfo->reponse = $mailsec_info['reponse'];
        $mailSecInfo->has_reponse = $mailSecInfo->reponse ? true : false;

        $mailSecInfo->email = $mailsec_info['email'];

        $mailSecInfo->id_e =
            $this->getInstance(DocumentEntite::class)->getEntiteWithRole($mailSecInfo->id_d, 'editeur');
        $mailSecInfo->denomination_entite = get_hecho($this->getEntiteSQL()->getInfo($mailSecInfo->id_e)['denomination']);
        $mailSecInfo->type_document = $this->getInstance(Document::class)->getInfo($mailSecInfo->id_d)['type'];

        $mailSecInfo->flux_destinataire = $this->getFluxDestinataire($mailSecInfo->type_document);

        $mailSecInfo->donneesFormulaire =
            $this->getDonneesFormulaireFactory()->get($mailSecInfo->id_d, $mailSecInfo->flux_destinataire);


        $this->validatePassword($mailSecInfo->donneesFormulaire, $mailSecInfo->key);
        $this->getDocumentEmail()->consulter($mailSecInfo->key, $this->getJournal());

        $this->getActionExecutorFactory()->executeOnDocument(
            $mailSecInfo->id_e,
            0,
            $mailSecInfo->id_d,
            'compute_read_mail'
        );
        $mailSecInfo->donneesFormulaire->getFormulaire()->setTabNumber(0);
        $mailSecInfo->fieldDataList = $mailSecInfo->donneesFormulaire->getFieldDataList('', 0);

        $mailSecInfo->flux_reponse = $this->getFluxReponse(
            $mailSecInfo->type_document,
            $mailSecInfo->type_destinataire
        );

        $mailSecInfo->has_flux_reponse = $mailSecInfo->flux_reponse ? true : false;

        if ($mailSecInfo->has_flux_reponse) {
            $documentEmailReponseSQL = $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class);
            $mailSecInfo->id_d_reponse = $documentEmailReponseSQL->getDocumentReponseId($mailSecInfo->id_de);

            $mailSecInfo->has_reponse = $documentEmailReponseSQL->getInfo($mailSecInfo->id_de)['has_reponse'];

            $mailSecInfo->donneesFormulaireReponse =
                $this->getDonneesFormulaireFactory()->get($mailSecInfo->id_d_reponse, $mailSecInfo->flux_reponse);

            $mailSecInfo->donneesFormulaireReponse->getFormulaire()->setTabNumber(0);
            $mailSecInfo->fieldDataListReponse =
                $mailSecInfo->donneesFormulaireReponse->getFieldDataList("", 0);
        }

        return $mailSecInfo;
    }


    private function renderPage(MailSecInfo $mailSecInfo)
    {
        $this->{'mailSecInfo'} = $mailSecInfo;
        $this->{'manifest_info'} = $this->getManifestFactory()->getPastellManifest()->getInfo();
        $this->{'recuperation_fichier_url'} = "recuperation-fichier.php?key={$mailSecInfo->key}";
        $this->{'reponse_recuperation_fichier_url'} = "recuperation-fichier.php?key={$mailSecInfo->key}&fichier_reponse=true";
        $this->{'id_e'} = $mailSecInfo->id_e;
        $this->{'my_role'} = "";

        $this->render("PageWebSec");
    }

    /**
     * @throws Exception
     */
    public function indexAction()
    {
        $mailSecInfo = $this->getMailsecInfo();
        $this->{'page_title'} = $mailSecInfo->denomination_entite . " - Mail sécurisé";
        $this->{'template_milieu'} = "MailSecIndex";
        $this->{'reponse_url'}  = WEBSEC_BASE . "/repondre.php?key={$mailSecInfo->key}";

        $this->{'inject'} = array(
            'id_e' => $mailSecInfo->id_e,
            'id_d' => $mailSecInfo->id_d,
            'action' => '',
            'id_ce' => false,
            'key' => $mailSecInfo->key
        );
        $this->{'download_all_link'} = WEBSEC_BASE . "/download_all.php?key={$mailSecInfo->key}";
        $this->renderPage($mailSecInfo);
    }

    /**
     * @param MailSecInfo $mailSecInfo
     * @return string
     * @throws Exception
     */
    private function createDocumentReponse(MailSecInfo $mailSecInfo)
    {
        $documentCreationService = $this->getObjectInstancier()->getInstance(DocumentCreationService::class);
        $id_d_reponse = $documentCreationService->createDocumentWithoutAuthorizationChecking(
            $mailSecInfo->id_e,
            $mailSecInfo->flux_reponse
        );

        $documentEmailReponseSQL = $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class);
        $documentEmailReponseSQL->addDocumentReponseId($mailSecInfo->id_de, $id_d_reponse);
        return $id_d_reponse;
    }


    private function checkResponseCanBeEdited(MailSecInfo $mailSecInfo)
    {
        if (! $mailSecInfo->has_flux_reponse || $mailSecInfo->has_reponse) {
            $this->redirectWebMailsec("index.php?key={$mailSecInfo->key}");
        }
    }
    /**
     * @throws Exception
     */
    public function repondreAction()
    {
        $mailSecInfo = $this->getMailsecInfo();
        $this->checkResponseCanBeEdited($mailSecInfo);

        $this->{'page_title'} = $mailSecInfo->denomination_entite . " - Réponse à un mail sécurisé";
        $this->{'template_milieu'} = "MailSecRepondre";

        $this->{'suppression_fichier_url'} = "suppression-fichier.php?key={$mailSecInfo->key}";

        $documentEmailReponseSQL = $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class);
        $id_d_reponse = $documentEmailReponseSQL->getDocumentReponseId($mailSecInfo->id_de);

        if (! $id_d_reponse) {
            $id_d_reponse = $this->createDocumentReponse($mailSecInfo);
        }

        $this->{'action_url'} = "reponse-edition.php";
        $this->{'inject'} = array(
            'id_e' => $mailSecInfo->id_e,
            'id_d' => $id_d_reponse,
            'action' => '',
            'id_ce' => '',
            'key' => $mailSecInfo->key
        );
        $this->{'page'} = 0;

        $this->renderPage($mailSecInfo);
    }

    /**
     * @throws Exception
     */
    public function reponseEditionAction()
    {
        $mailSecInfo = $this->getMailsecInfo();
        $this->checkResponseCanBeEdited($mailSecInfo);

        $fileUploader = new FileUploader();
        $mailSecInfo->donneesFormulaireReponse->saveTab($this->getPostInfo(), $fileUploader, 0);

        $this->getObjectInstancier()->getInstance(Document::class)->setTitre(
            $mailSecInfo->id_d_reponse,
            $mailSecInfo->donneesFormulaireReponse->getTitre()
        );

        if ($this->getPostOrGetInfo()->get('ajouter') == 'ajouter') {
            $this->redirectWebMailsec("repondre.php?key={$mailSecInfo->key}");
        }

        if (! $mailSecInfo->donneesFormulaireReponse->isValidable()) {
            $this->setLastError($mailSecInfo->donneesFormulaireReponse->getLastError());
            $this->redirectWebMailsec("repondre.php?key={$mailSecInfo->key}");
        }
        $this->redirectWebMailsec("validation.php?key={$mailSecInfo->key}");
    }

    /**
     * @throws Exception
     */
    public function validationAction()
    {
        $mailSecInfo = $this->getMailsecInfo();
        $this->checkResponseCanBeEdited($mailSecInfo);

        $this->{'page_title'} = $mailSecInfo->denomination_entite . " - Mail sécurisé - Validation de la réponse";
        $this->{'template_milieu'} = "MailSecValidation";
        $this->{'reponse_url'}  = WEBSEC_BASE . "/repondre.php?key={$mailSecInfo->key}";
        $this->{'validation_url'}  = WEBSEC_BASE . "/do-validation.php?key={$mailSecInfo->key}";
        $this->{'download_all_link'} = WEBSEC_BASE . "/download_all.php?key={$mailSecInfo->key}";
        $this->{'inject'} = array(
            'id_e' => $mailSecInfo->id_e,
            'id_d' => $mailSecInfo->id_d,
            'action' => '',
            'id_ce' => '',
            'key' => $mailSecInfo->key
        );
        $this->renderPage($mailSecInfo);
    }

    /**
     * @throws Exception
     */
    public function doValidationAction()
    {
        $mailSecInfo = $this->getMailsecInfo();
        $this->checkResponseCanBeEdited($mailSecInfo);

        /** Pour des raisons de compatibilité */
        if (
            $this
            ->getDocumentTypeFactory()
            ->getFluxDocumentType($mailSecInfo->type_document)
            ->getAction()
            ->getActionClass('modification-reponse')
        ) {
            $result = $this->getActionExecutorFactory()->executeOnDocument(
                $mailSecInfo->id_e,
                -1,
                $mailSecInfo->id_d,
                'modification-reponse',
                [],
                false,
                ['mailSecInfo' => $mailSecInfo]
            );
            if (! $result) {
                $this->setLastError($this->getActionExecutorFactory()->getLastMessage());
                $this->redirectWebMailsec("repondre.php?key={$mailSecInfo->key}");
            }
        }

        $this->getActionExecutorFactory()->executeOnDocument(
            $mailSecInfo->id_e,
            0,
            $mailSecInfo->id_d,
            'compute_answered_mail'
        );

        $this->getObjectInstancier()->getInstance(ActionCreatorSQL::class)->addAction(
            $mailSecInfo->id_e,
            0,
            "validation",
            "Validation du document par {$mailSecInfo->email}",
            $mailSecInfo->id_d_reponse
        );

        $titre = $mailSecInfo->donneesFormulaireReponse->getTitre();

        $this->getJournal()->add(
            Journal::MAIL_SECURISE,
            $mailSecInfo->id_e,
            $mailSecInfo->id_d_reponse,
            "Validation",
            "{$mailSecInfo->email} a validé le document $titre (id_de = {$mailSecInfo->id_de})"
        );

        $this->getJournal()->add(
            Journal::MAIL_SECURISE,
            $mailSecInfo->id_e,
            $mailSecInfo->id_d,
            "Validation",
            "{$mailSecInfo->email} a validé une réponse pour le document $titre (id_de = {$mailSecInfo->id_de})"
        );

        $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class)->validateReponse($mailSecInfo->id_de);

        $notificationMail = $this->getObjectInstancier()->getInstance(NotificationMail::class);
        $notificationMail->notify(
            $mailSecInfo->id_e,
            $mailSecInfo->id_d,
            'reponse',
            $mailSecInfo->type_document,
            "Une réponse a été apportée à ce mail sécurisé."
        );

        $this->setLastMessage("Votre réponse a été envoyée");
        $this->redirectWebMailsec("index.php?key={$mailSecInfo->key}");
    }

    public function passwordAction()
    {
        $recuperateur = $this->getGetInfo();
        $key = $recuperateur->get('key');
        $info  = $this->getDocumentEmail()->getInfoFromKey($key);
        if (! $info) {
            $this->redirectWebMailsec("invalid.php");
        }

        $this->{'page'} = "Mail sécurisé";
        $this->{'page_title'} = " Mail sécurisé";
        $this->{'the_key'} = $key;
        $this->{'template_milieu'} = "MailSecPassword";
        $this->{'manifest_info'} = $this->getManifestFactory()->getPastellManifest()->getInfo();
        $this->render("PageWebSec");
    }

    public function invalidAction()
    {
        $this->{'page'} = "Mail sécurisé";
        $this->{'page_title'} = " Mail sécurisé";
        $this->{'template_milieu'} = "MailSecInvalid";
        $this->{'manifest_info'} = $this->getManifestFactory()->getPastellManifest()->getInfo();
        $this->render("PageWebSec");
    }

    /**
     * @throws Exception
     */
    public function chunkUploadAction()
    {
        $mailSecInfo = $this->getMailsecInfo();
        $this->checkResponseCanBeEdited($mailSecInfo);
        $this->getObjectInstancier()->getInstance(DonneesFormulaireControler::class)->chunkUploadAction();
    }

    /**
     * @throws Exception
     */
    public function suppressionFichierAction()
    {
        $mailSecInfo = $this->getMailsecInfo();
        $this->checkResponseCanBeEdited($mailSecInfo);

        $field = $this->getPostOrGetInfo()->get('field');
        $num = $this->getPostOrGetInfo()->getInt('num');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($mailSecInfo->id_d_reponse, $mailSecInfo->flux_reponse);
        $donneesFormulaire->removeFile($field, $num);

        $this->redirectWebMailsec("repondre.php?key={$mailSecInfo->key}");
    }

    /**
     * @throws Exception
     */
    public function recuperationFichierAction()
    {
        $mailSecInfo = $this->getMailsecInfo();
        $field = $this->getPostOrGetInfo()->get('field');
        $num = $this->getPostOrGetInfo()->getInt('num');
        $fichier_reponse = $this->getPostOrGetInfo()->get('fichier_reponse');

        if ($fichier_reponse) {
            $file_path = $mailSecInfo->donneesFormulaireReponse->getFilePath($field, $num);
            $file_name = $mailSecInfo->donneesFormulaireReponse->getFileName($field, $num);
        } else {
            $file_path = $mailSecInfo->donneesFormulaire->getFilePath($field, $num);
            $file_name = $mailSecInfo->donneesFormulaire->getFileName($field, $num);
        }

        if (! file_exists($file_path)) {
            $this->setLastError("Ce fichier n'existe pas");
            $this->redirectWebMailsec("index.php?key={$mailSecInfo->key}");
        }

        $this->getJournal()->add(
            Journal::DOCUMENT_CONSULTATION,
            $mailSecInfo->id_e,
            $mailSecInfo->id_d,
            "Consulté",
            "{$mailSecInfo->email} a consulté le document $file_name"
        );

        header_wrapper("Content-type: " . mime_content_type($file_path));
        header_wrapper("Content-disposition: attachment; filename=\"$file_name\"");
        header_wrapper("Expires: 0");
        header_wrapper("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header_wrapper("Pragma: public");

        readfile($file_path);
    }

    /**
     * @throws Exception
     */
    public function downloadAllAction()
    {
        $mailSecInfo = $this->getMailsecInfo();
        $field = $this->getPostOrGetInfo()->get('field');

        $fichier_reponse = $this->getPostOrGetInfo()->get('fichier_reponse');
        if ($fichier_reponse) {
            $id_d = $mailSecInfo->id_d_reponse;
        } else {
            $id_d = $mailSecInfo->id_d;
        }

        $this->getObjectInstancier()->getInstance(DonneesFormulaireControler::class)->downloadAll(
            $mailSecInfo->id_e,
            $id_d,
            false,
            $field
        );
    }
}
