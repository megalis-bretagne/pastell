<?php
class IParapheurMAJCertif extends ChoiceActionExecutor {

	/**
	 * @return bool
	 * @throws Exception
	 */
    public function go(){
		$id_ce_list = $this->getRecuperateur()->get('id_ce_list');
		if (!$id_ce_list){
			$this->displayErrorAndRedirect("Aucun connecteur sélectionné");
		}

		$fileUploader = new FileUploader();
		$user_certificate = $fileUploader->getFileContent('user_certificat');
        $user_certificat_password = $this->getRecuperateur()->get('user_certificat_password');

        if (! $user_certificate){
            $this->displayErrorAndRedirect("Il faut sélectionner un certificat");
        }

        if(!$user_certificat_password) {
            $this->displayErrorAndRedirect('Il faut renseigner le mot de passe');
        }

        foreach($id_ce_list as $id_ce){
            $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
            if($user_certificate){
                $user_certificate_name = $fileUploader->getName('user_certificat');
				$connecteurConfig->addFileFromData(
					'iparapheur_user_certificat',
					$user_certificate_name,
					$user_certificate
				);
				$connecteurConfig->setData('iparapheur_user_certificat_password', $user_certificat_password);
				$this->objectInstancier
					->getInstance(ActionExecutorFactory::class)
					->executeOnConnecteur($id_ce,$this->id_u,'update-certificate');
			}
		}
		$lastMessage = $this->objectInstancier->getInstance(LastMessage::class);
		$lastMessage->setLastMessage("Le(s) certificat(s) a été remplacé(s)");
		$this->redirect("/Connecteur/externalData?id_ce={$this->id_ce}&field=changement-certificat");
		return true;
    }

    public function displayAPI(){
        return array();
    }

    public function display(){
        $all_connecteur = array();
        $connecteur_list = $this->objectInstancier
			->getInstance(ConnecteurEntiteSQL::class)
			->getAllById('iParapheur');
        foreach($connecteur_list as $connecteur){
            if (! $connecteur['id_e']){
                continue;
            }
            $all_connecteur[] = $connecteur;
        }

        $this->{'all_connecteur'} = $all_connecteur;

        $this->renderPage(
        	"Mise à jour certificat iParapheur",
			__DIR__."/../template/IParapheurChoixMAJCertificat.php"
		);
        return true;
    }
    
	private function displayErrorAndRedirect($error_message){
		$lastError = $this->objectInstancier->getInstance(LastError::class);
		$lastError->setLastMessage($error_message);
		$this->redirect("/Connecteur/externalData?id_ce={$this->id_ce}&field=changement_certificat");
	}
}