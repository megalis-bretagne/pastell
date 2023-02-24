<?php

class GlobalUpdateCertificate extends ConnecteurTypeChoiceActionExecutor
{
    public const DISPLAY_LIMIT = 500;
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $recuperateur = $this->getRecuperateur();
        $id_ce_list = $recuperateur->get('id_ce_list');
        if (!$id_ce_list) {
            $this->displayErrorAndRedirect('Aucun connecteur sélectionné');
        }

        $user_certificat_input = 'user_certificat';
        $user_certificat_password_input = 'user_certificat_password';

        $fileUploader = new FileUploader();
        $certificate = $fileUploader->getFileContent($user_certificat_input);
        $certificate_name = $fileUploader->getName($user_certificat_input);
        $certificate_password = $recuperateur->get($user_certificat_password_input);

        if (!$certificate) {
            $this->displayErrorAndRedirect('Il faut choisir un certificat');
        }
        if (!$certificate_password) {
            $this->displayErrorAndRedirect('Il faut renseigner le mot de passe');
        }

        $certificate_field = $this->getMappingValue('certificate');
        $certificate_password_field = $this->getMappingValue('certificate_password');
        $update_certificate_action = $this->getMappingValue('update-certificate');

        foreach ($id_ce_list as $id_ce) {
            $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
            $connecteurConfig->addFileFromData($certificate_field, $certificate_name, $certificate);
            $connecteurConfig->setData($certificate_password_field, $certificate_password);
            $this->objectInstancier
                ->getInstance(ActionExecutorFactory::class)
                ->executeOnConnecteur($id_ce, $this->id_u, $update_certificate_action);
        }
        $lastMessage = $this->objectInstancier->getInstance(LastMessage::class);
        $lastMessage->setLastMessage('Le certificat a été remplacé');
        $this->redirect("/Connecteur/externalData?id_ce={$this->id_ce}&field=changement_certificat");
        return true;
    }

    public function displayAPI()
    {
        return [];
    }

    public function display()
    {
        $pageTitle = $this->getMappingValue('page_title');
        $offset = $this->getRecuperateur()->getInt('offset', 0);
        $limit = self::DISPLAY_LIMIT;

        $connecteurEntiteSQL = $this->objectInstancier->getInstance(ConnecteurEntiteSQL::class);
        $this->setViewParameter('connectors', $connecteurEntiteSQL->getAllEntiteConnectorById($this->type, $offset, $limit));
        $this->setViewParameter('count', $connecteurEntiteSQL->getCountAllEntiteConnectorById($this->type));
        $this->setViewParameter('offset', $offset);
        $this->setViewParameter('limit', $limit);

        $this->renderPage($pageTitle, __DIR__ . '/template/GlobalUpdateCertificate.php');
        return true;
    }

    private function displayErrorAndRedirect($error_message)
    {
        $lastError = $this->objectInstancier->getInstance(LastError::class);
        $lastError->setLastMessage($error_message);
        $this->redirect("/Connecteur/externalData?id_ce={$this->id_ce}&field=changement_certificat");
    }
}
