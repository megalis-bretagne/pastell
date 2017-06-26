<?php
class S2lowMAJCertif extends ChoiceActionExecutor {
	
	
	public function go(){
		$recuperateur = new Recuperateur($_POST);
		
		$go =$recuperateur->get('go');
		if (! $go){
			$this->display();
		}
		
		return $this->updateCertificate();
	}
	
	public function displayAPI(){
		return array();
	}
	
	public function display(){
		$all_connecteur = array();
		foreach($this->objectInstancier->ConnecteurEntiteSQL->getAllById('s2low') as $connecteur){
			if (! $connecteur['id_e']){
				continue;
			}
			$all_connecteur[] = $connecteur;
		}
		
		$this->all_connecteur = $all_connecteur;

		$this->renderPage("Mise à jour certificat S2low", __DIR__."/../template/S2lowChoixMAJCertificat.php");
		exit;
	}
	
	private function updateCertificate(){
		$recuperateur = new Recuperateur($_POST);
		$id_ce_list = $recuperateur->get('id_ce_list');
		if (!$id_ce_list){
			throw new Exception("Aucun connecteur sélectionné");
		}
		
		$fileUploader = new FileUploader();
		$server_certificate = $fileUploader->getFileContent('server_certificate');
		$user_certificate = $fileUploader->getFileContent('user_certificat');
		
		if (!$server_certificate && ! $user_certificate){
			throw new Exception("Il faut sélectionné au moins un certificat");
		}
		
		foreach($id_ce_list as $id_ce){
			$connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
			if ($server_certificate){
				$server_certificate_name = $fileUploader->getName('server_certificate');
				$connecteurConfig->addFileFromData('server_certificate', $server_certificate_name, $server_certificate);
			}
			if($user_certificate){
				$user_certificate_name = $fileUploader->getName('user_certificat');
				$user_certificat_password = $recuperateur->get('user_certificat_password');
				$connecteurConfig->addFileFromData('user_certificat', $user_certificate_name, $user_certificate);
				$connecteurConfig->setData('user_certificat_password', $user_certificat_password);
				$this->objectInstancier->ActionExecutorFactory->executeOnConnecteur($id_ce,$this->id_u,'update-certificate');
			}
		}
		
		$this->setLastMessage("Le(s) certificat(s) a été remplacé(s)");
		return true;
	}
	
	
}