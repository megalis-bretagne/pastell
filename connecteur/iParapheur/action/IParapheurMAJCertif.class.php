<?php
class IParapheurMAJCertif extends ChoiceActionExecutor {


	/**
	 * @return bool
	 * @throws Exception
	 */
    public function go(){
        $go = $this->getRecuperateur()->get('go');
        if (! $go){
            $this->display();
            return true;
        }

        return $this->updateCertificate();
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

	/**
	 * @return bool
	 * @throws Exception
	 */
    private function updateCertificate(){
        $id_ce_list = $this->getRecuperateur()->get('id_ce_list');
        if (!$id_ce_list){
            throw new UnrecoverableException("Aucun connecteur sélectionné");
        }

        $fileUploader = new FileUploader();
        $user_certificate = $fileUploader->getFileContent('user_certificat');

        if (! $user_certificate){
            throw new UnrecoverableException("Il faut sélectionner un certificat");
        }

        foreach($id_ce_list as $id_ce){
            $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
            if($user_certificate){
                $user_certificate_name = $fileUploader->getName('user_certificat');
                $user_certificat_password = $this->getRecuperateur()->get('user_certificat_password');
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

        $this->setLastMessage("Le(s) certificat(s) a été remplacé(s)");
        return true;
    }


}