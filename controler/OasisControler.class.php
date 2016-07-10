<?php


class OasisControler extends PastellControler {

	public function cancelAction(){

		$x_hub_signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
		$rawdata = file_get_contents('php://input');

		file_put_contents("/tmp/pastell-oasis.tmp", $x_hub_signature.$rawdata);

		/** @var OasisProvisionning $oasisProvisionning */
		$oasisProvisionning = $this->getConnecteurFactory()->getGlobalConnecteur('oasis-provisionning');
		if (!$oasisProvisionning){
			http_response_code(400);
			echo "Aucun connecteur Oasis Provisionning trouvé";
			exit;
		}

		if (empty($_SERVER['HTTP_X_HUB_SIGNATURE'])){
			http_response_code(400);
			echo "L'entete X-Hub-Signature n'a pas été trouvée";
			exit;
		}


		try {
			$instance_id = $oasisProvisionning->getInstanceIdFromDeleteInstanceMessage($rawdata,$x_hub_signature);
			$selected_id_e = false;
			foreach($this->getConnecteurEntiteSQL()->getAllById("openid-authentication") as $connecteur_info){
				$connecteur_config = $this->getConnecteurFactory()->getConnecteurConfig($connecteur_info['id_ce']);
				if ($connecteur_config->get("instance_id") == $instance_id){
					$selected_id_e = $connecteur_info['id_e'];
					break;
				}
			}
			if (! $selected_id_e){
				throw new Exception("Impossible de trouvé une entité correspondante à l'instance_id $instance_id");
			}

			$this->getEntiteSQL()->setActive($selected_id_e,0);

		} catch (Exception $e){
			http_response_code(400);
			echo $e->getMessage();
			exit;
		}
		echo "ok";
	}

	public function connexionAction(){
		$recuperateur = new Recuperateur();

		$id_e = $recuperateur->get('id_e');

		if (!$id_e){
			$this->setLastError("Une erreur est survenue");
			$this->redirect("/Connexion/connexion");
		}

		/** @var OpenIDAuthentication $openID */
		$openID = $this->getConnecteurFactory()->getConnecteurByType($id_e,'openid-authentification','openid-authentication');
		if (!$openID){
			$this->setLastError("Une erreur est survenue - pas de connecteur");
			$this->redirect("/Connexion/connexion");
		}

		$openID->authenticate();
	}

	public function instanciationAction(){

		/** @var OasisProvisionning $oasisProvisionning */
		$oasisProvisionning = $this->getConnecteurFactory()->getGlobalConnecteur('oasis-provisionning');
		if (!$oasisProvisionning){
			http_response_code(400);
			echo "Aucun connecteur Oasis Provisionning trouvé";
			exit;
		}

		if (empty($_SERVER['HTTP_X_HUB_SIGNATURE'])){
			http_response_code(400);
			echo "L'entete X-Hub-Signature n'a pas été trouvée";
			exit;
		}

		$x_hub_signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
		$rawdata = file_get_contents('php://input');

		try {
			$oasisProvisionning->addInstance($rawdata,$x_hub_signature);

			$this->getJournal()->add(Journal::CONNEXION,0,0,'instanciation', "Nouvelle demande de provisionning Oasis ajouté");

		} catch (Exception $e){
			http_response_code(400);
			echo $e->getMessage();
			exit;
		}
		echo "ok";


	}

}