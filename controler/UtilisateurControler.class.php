<?php
class UtilisateurControler extends PastellControler {

	/**
	 * @return UtilisateurNewEmailSQL
	 */
	public function getUtilisateurNewEmailSQL(){
		return $this->getInstance("UtilisateurNewEmailSQL");
	}

	/**
	 * @return NotificationMail
	 */
	public function getNotificationMail(){
		return $this->getInstance("NotificationMail");
	}

	/**
	 * @return Notification
	 */
	public function getNotification(){
		return $this->getInstance("Notification");
	}

	public function modifPasswordAction(){
		$authentificationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur("authentification");
		if ($authentificationConnecteur){
			$this->{'LastError'}->setLastError("Vous ne pouvez pas modifier votre mot de passe en dehors du CAS");
			$this->redirect("/Utilisateur/moi");
		}
		
		$this->{'page_title'} = "Modification de votre mot de passe";
		$this->{'template_milieu'} = "UtilisateurModifPassword";
		$this->renderDefault();
	}
	
	public function modifEmailAction(){
		$this->{'utilisateur_info'} = $this->getUtilisateur()->getInfo($this->getId_u());
		if ($this->{'utilisateur_info'}['id_e'] == 0){
			$this->{'LastError'}->setLastError("Les utilisateurs de l'entité racine ne peuvent pas utiliser cette procédure");
			$this->redirect("/Utilisateur/moi");
		}
		$this->{'page_title'} = "Modification de votre email";
		$this->{'template_milieu'} = "UtilisateurModifEmail";
		$this->renderDefault();
	}
	
	public function modifEmailControlerAction(){
		$recuperateur = new Recuperateur($_POST);
		$password = $recuperateur->get('password');
		if ( ! $this->getUtilisateur()->verifPassword($this->getId_u(),$password)){
			$this->{'LastError'}->setLastError("Le mot de passe est incorrect.");
			$this->redirect("/Utilisateur/modifEmail");
		}
		$email = $recuperateur->get('email');
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$this->{'LastError'}->setLastError("L'email que vous avez saisi ne semble pas être valide");
			$this->redirect("/Utilisateur/modifEmail");
		}
		
		$utilisateur_info = $this->getUtilisateur()->getInfo($this->getId_u()); 
		
		
		$password = $this->getUtilisateurNewEmailSQL()->add($this->getId_u(),$email);
		
		$zenMail = $this->getZenMail();
		$zenMail->setEmetteur("Pastell",PLATEFORME_MAIL);
		$zenMail->setDestinataire($email);
		$zenMail->setSujet("Changement de mail sur Pastell");
		$info = array("password" => $password);
		$zenMail->setContenu(PASTELL_PATH . "/mail/changement-email.php",$info);
		$zenMail->send();
		
		$this->getJournal()->add(Journal::MODIFICATION_UTILISATEUR,$utilisateur_info['id_e'],0,"change-email","Demande de changement d'email initiée {$utilisateur_info['email']} -> $email");

		$this->setLastMessage("Un email a été envoyé à votre nouvelle adresse. Merci de le consulter pour la suite de la procédure.");
		$this->redirect("/Utilisateur/moi");
	}
	
	public function modifEmailConfirmAction(){
		$recuperateur = new Recuperateur($_GET);
		$password = $recuperateur->get('password');
		$info = $this->getUtilisateurNewEmailSQL()->confirm($password);
		if ($info){
			$this->createChangementEmail($info['id_u'],$info['email']);
		}

		$this->getUtilisateurNewEmailSQL()->delete($info['id_u']);
		$this->{'result'}= $info;
		$this->{'page_title'}= "Procédure de changement d'email";
		$this->{'template_milieu'}= "UtilisateurModifEmailConfirm";
		$this->renderDefault();
	}
	
	private function createChangementEmail($id_u,$email){
		$id_d = $this->getDocument()->getNewId();	
		$this->getDocument()->save($id_d,'changement-email');
		$utilisateur_info = $this->getUtilisateur()->getInfo($id_u); 
		
		$this->getDocument()->setTitre($id_d,$utilisateur_info['login']);
		$this->getDocumentEntite()->addRole($id_d,$utilisateur_info['id_e'],"editeur");
		$actionCreator = new ActionCreator($this->getSQLQuery(),$this->getJournal(),$id_d);
		$actionCreator->addAction($utilisateur_info['id_e'],$id_u,Action::CREATION,"Création du document");

		/** @var DonneesFormulaire $donneesFormulaire */
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
		foreach(array('id_u','login','nom','prenom') as $key){
			$data[$key] = $utilisateur_info[$key];
		}
		$data['email_actuel'] = $utilisateur_info['email'];
		$data['email_demande'] = $email;
		$donneesFormulaire->setTabData($data);
		
		$this->getNotificationMail()->notify($utilisateur_info['id_e'],$id_d,'creation','changement-email',$utilisateur_info['login']." a fait une demande de changement d'email");
	}
	
	public function certificatAction(){
		$recuperateur = new Recuperateur($_GET);
		$this->{'verif_number'}= $recuperateur->get('verif_number');
		$this->{'offset'}= $recuperateur->getInt('offset',0);
	
		$this->{'limit'}= 20;
		
		$this->{'count'}= $this->getUtilisateurListe()->getNbUtilisateurByCertificat($this->{'verif_number'});
		$this->{'liste'}= $this->getUtilisateurListe()->getUtilisateurByCertificat($this->{'verif_number'},$this->{'offset'},$this->{'limit'});
		
		if (! $this->{'count'}){
			$this->redirect("/index.php");
		}
		
		$this->{'certificat'}= new Certificat($this->{'liste'}[0]['certificat']);
		$this->{'certificatInfo'}= $this->{'certificat'}->getInfo();
		
		$this->{'page_title'}= "Certificat";
		$this->{'template_milieu'}= "UtilisateurCertificat";
		$this->renderDefault();
	}
	
	public function editionAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_u = $recuperateur->get('id_u');
		$id_e = $recuperateur->getInt('id_e');
		
		$infoUtilisateur = array('login' =>  $this->getLastError()->getLastInput('login'),
							'nom' =>  $this->getLastError()->getLastInput('nom'),
							'prenom' =>  $this->getLastError()->getLastInput('prenom'),
							'email'=> $this->getLastError()->getLastInput('email'),
							'certificat' => '',
							'id_e' => $id_e,
		);
		
		if ($id_u){
			$infoUtilisateur = $this->getUtilisateur()->getInfo($id_u);
			if (! $infoUtilisateur){
				$this->redirect();
			}
		}
		
		$this->verifDroit($infoUtilisateur['id_e'], "utilisateur:edition");

		$this->{'infoEntite'}= $this->getEntiteSQL()->getInfo($infoUtilisateur['id_e']);
		$this->{'certificat'}= new Certificat($infoUtilisateur['certificat']);
		$this->{'arbre'}= $this->getRoleUtilisateur()->getArbreFille($this->getId_u(),"entite:edition");
		
		if ($id_u){
			$this->{'page_title'}= "Modification de " .  $infoUtilisateur['prenom']." ". $infoUtilisateur['nom'];
		} else {
			$this->{'page_title'}= "Nouvel utilisateur ";
		}
		$this->{'id_u'}= $id_u;
		$this->{'id_e'}= $id_e;
		$this->{'infoUtilisateur'}= $infoUtilisateur;
		$this->{'template_milieu'}= "UtilisateurEdition";
		$this->renderDefault();
	}
	
	public function detailAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_u = $recuperateur->get('id_u');
		
		$info = $this->getUtilisateur()->getInfo($id_u);
		if (! $info){
			$this->setLastError("Utilisateur $id_u inconnu");
			$this->redirect("index.php");
		}
		
		$this->{'certificat'}= new Certificat($info['certificat']);
		$this->{'page_title'}= "Utilisateur ".$info['prenom']." " . $info['nom'];
		$this->{'entiteListe'}= $this->getEntiteListe();
		$this->{'tabEntite'}= $this->getRoleUtilisateur()->getEntite($this->getId_u(),'entite:edition');
		
		$this->{'notification_list'}= $this->getNotificationList($id_u);
		
		$this->getRoleUtilisateur()->getRole($id_u);
		
		
		if ( ! $this->getRoleUtilisateur()->hasDroit($this->getId_u(),"utilisateur:lecture",$info['id_e'])) {
			$this->setLastError("Vous n'avez pas le droit de lecture (".$info['id_e'].")");
			$this->redirect();
		}
		$this->{'utilisateur_edition'}= $this->getRoleUtilisateur()->hasDroit($this->getId_u(),"utilisateur:edition",$info['id_e']);
		
		if( $info['id_e'] ){
			$this->{'infoEntiteDeBase'}= $this->getEntiteSQL()->getInfo($info['id_e']);
			$this->{'denominationEntiteDeBase'}= $this->{'infoEntiteDeBase'}['denomination'];
		}
		$this->{'info'}= $info;
		$this->{'id_u'}= $id_u;
		$this->{'arbre'}= $this->getRoleUtilisateur()->getArbreFille($this->getId_u(),"entite:edition");
		$this->{'template_milieu'}= "UtilisateurDetail";
		$this->renderDefault();
	}
		
	private function getNotificationList($id_u){
		$result  = $this->getNotification()->getAll($id_u);
		foreach($result as $i => $line){
			$action  = $this->getDocumentTypeFactory()->getFluxDocumentType($line['type'])->getAction();
			foreach($line['action'] as $j => $action_id){
				$result[$i]['action'][$j] = $action->getActionName($action_id);
			}
		}
		return $result;
	}
	
	public function moiAction(){
		$id_u = $this->getId_u();
		$info = $this->getUtilisateur()->getInfo($id_u);
		$this->{'certificat'}= new Certificat($info['certificat']);
		
		$this->{'page_title'}= "Espace utilisateur : ".$info['prenom']." " . $info['nom'];
		
		$this->{'entiteListe'}= $this->getEntiteListe();
		
		$this->{'tabEntite'}= $this->getRoleUtilisateur()->getEntite($this->getId_u(),'entite:edition');
		
		$this->{'notification_list'}= $this->getNotificationList($id_u);
		
		$this->{'roleInfo'}=  $this->getRoleUtilisateur()->getRole($id_u);
		$this->{'utilisateur_edition'}= $this->getRoleUtilisateur()->hasDroit($this->getId_u(),"utilisateur:edition",$info['id_e']);
		
		if( $info['id_e'] ){
			$infoEntiteDeBase = $this->getEntiteSQL()->getInfo($info['id_e']);
			$this->{'denominationEntiteDeBase'}= $infoEntiteDeBase['denomination'];
		}
		$this->{'info'}= $info;
		$this->{'id_u'}= $id_u;
		$this->{'arbre'}= $this->getRoleUtilisateur()->getArbreFille($this->getId_u(),"entite:lecture");
		$this->{'template_milieu'}= "UtilisateurMoi";
		$this->renderDefault();
	}
	
        
        // Prise en compte du paramètre $message dans l'affectation de l'erreur
        // Correction "lastError"        
	private function redirectEdition($id_e,$id_u,$message){
		$this->setLastError($message);
		$this->redirect("/Utilisateur/edition?id_e=$id_e&id_u=$id_u");
	}

	public function doEditionAction(){
		$recuperateur = new Recuperateur($_POST);

		$id_e = $recuperateur->getInt('id_e');
		$id_u = $recuperateur->get('id_u');

		$password = $recuperateur->get('password');
		$password2 = $recuperateur->get('password2');

		try {

			if ( $password && $password2 && ($password != $password2) ){
				//La vérification du mot de passe ne concerne que la partie web et n'est pas vérifié par l'API
				throw new Exception("Les mots de passe ne correspondent pas");
			}
			/** @var UtilisateurAPIController $utilisateurAPIController */
			$utilisateurAPIController = $this->getAPIController('Utilisateur');
			$result = $utilisateurAPIController->editAction();
			$id_u = $result['id_u'];
		} catch (Exception $e){
			$this->redirectEdition($id_e,$id_u,$e->getMessage());
		}
		
		$this->redirect("/Utilisateur/detail?id_u=$id_u");
	}
	
	public function ajoutRoleAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_u = $recuperateur->get('id_u');
		$role = $recuperateur->get('role');
		$id_e = $recuperateur->get('id_e',0);

		$this->verifDroit($id_e,"entite:edition");
		if ($this->getRoleUtilisateur()->hasRole($id_u,$role,$id_e)){
			$this->setLastError("Ce droit a déjà été attribué à l'utilisateur");
		} elseif ($role){
			$this->getRoleUtilisateur()->addRole($id_u,$role,$id_e);	
		}
		$this->redirect("/Utilisateur/detail?id_u=$id_u");
	}
	
	public function supprimeRoleAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_u = $recuperateur->get('id_u');
		$role = $recuperateur->get('role');
		$id_e = $recuperateur->getInt('id_e',0);
		$this->verifDroit($id_e,"entite:edition");
		$this->getRoleUtilisateur()->removeRole($id_u,$role,$id_e);
		$this->redirect("/Utilisateur/detail?id_u=$id_u");
	}
	
	private function verifEditNotification($id_u,$id_e,$type){
		$utilisateur_info = $this->getUtilisateur()->getInfo($id_u);
	
		if (
				$this->getRoleUtilisateur()->hasDroit($this->getId_u(),"entite:edition",$id_e)
				&&
				$this->getRoleUtilisateur()->hasDroit($this->getId_u(),"utilisateur:edition",$utilisateur_info['id_e'])
		){
			return true;
		}
	
		if (
				$id_u == $this->getId_u()
				&&
				$this->getRoleUtilisateur()->hasDroit($this->getId_u(),"entite:lecture",$id_e)
				&&
				$this->getRoleUtilisateur()->hasDroit($this->getId_u(),"$type:lecture",$id_e)
		){
			return true;
		}
	
		$this->setLastError("Vous n'avez pas les droits nécessaires pour faire cela");
		$this->redirectToPageUtilisateur($id_u);
		return false;
	}
	
	private function redirectToPageUtilisateur($id_u){
		if ($id_u == $this->getId_u()){
			$this->redirect("/Utilisateur/moi");
		} else {
			$this->redirect("/Utilisateur/detail?id_u=$id_u");
		}
	}
	
	public function notificationAjoutAction(){
		$recuperateur = new Recuperateur($_POST);
		
		$id_u = $recuperateur->getInt('id_u');
		$id_e = $recuperateur->getInt('id_e',0);
		$type = $recuperateur->get('type',0);
		$daily_digest = $recuperateur->getInt('daily_digest',0);
		
		$this->verifEditNotification($id_u, $id_e,$type);
		$this->getNotification()->add($id_u,$id_e,$type,0,$daily_digest);
		$this->setLastMessage("La notification a été ajoutée");
		$this->redirectToPageUtilisateur($id_u);
	}
	
	public function notificationAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_u = $recuperateur->getInt('id_u');
		$id_e = $recuperateur->getInt('id_e');
		$type = $recuperateur->get('type');

		$utilisateur_info = $this->getUtilisateur()->getInfo($id_u);
		$this->verifEditNotification($id_u, $id_e,$type);
		
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
		$titreSelectAction = $type ? "Sélectionner les actions des documents de type ".$type:"La sélection des actions n'est pas possible car aucun type de document n'est spécifié";
		
		$action_list = $documentType->getAction()->getActionWithNotificationPossible();

		$this->{'titreSelectAction'}= $titreSelectAction;
		$this->{'action_list'}= $this->getNotification()->getNotificationActionList($id_u,$id_e,$type,$action_list);
		$this->{'id_u'}= $id_u;
		$this->{'id_e'}= $id_e;
		$this->{'type'}= $type;
		
		
		$this->{'page_title'}= get_hecho($utilisateur_info['login'])." - abonnement aux actions des documents " ;
		$this->{'template_milieu'}= "UtilisateurNotification";
		$this->renderDefault();
	}
	
	public function notificationSuppressionAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_n = $recuperateur->get('id_n');
		
		$infoNotification = $this->getNotification()->getInfo($id_n);
		$id_u = $infoNotification['id_u'];
		$id_e = $infoNotification['id_e'];
		$type = $infoNotification['type'];
		
		$this->verifEditNotification($id_u, $id_e,$type);
		$this->getNotification()->remove($id_n);
		$this->setLastMessage("La notification a été supprimée");
		$this->redirectToPageUtilisateur($id_u);
	}
	
	public function doNotificationEditAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_u = $recuperateur->getInt('id_u');
		$id_e = $recuperateur->getInt('id_e');
		$type = $recuperateur->get('type');
		
		$this->getUtilisateur()->getInfo($id_u);
		$this->verifEditNotification($id_u, $id_e,$type);
		
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
		
		$action_list = $documentType->getAction()->getActionWithNotificationPossible();
		
		$all_checked = true;
		$no_checked = false;
		$action_checked = array();
		foreach($action_list as $action){
			$checked = !! $recuperateur->get($action['id']);
			$action_checked[$action['id']] = $checked;
			$all_checked = $all_checked && $checked;
			$no_checked = $no_checked || $checked; 			
		}
		
		$daily_digest = $this->getNotification()->hasDailyDigest($id_u,$id_e,$type);
		
		$this->getNotification()->removeAll($id_u,$id_e,$type);
		
		$this->setLastMessage("Les notifications ont été modifiées");
		if (! $no_checked){
			$this->redirectToPageUtilisateur($id_u);
		}
		if ($all_checked){
			$this->getNotification()->add($id_u,$id_e,$type,Notification::ALL_TYPE,$daily_digest);
			$this->redirectToPageUtilisateur($id_u);
		}
		foreach($action_list as $action){
			if (! $action_checked[$action['id']]){
				continue;
			}
			$this->getNotification()->add($id_u,$id_e,$type,$action['id'],$daily_digest);
		}
		$this->redirectToPageUtilisateur($id_u);
	}
	
	public function notificationToogleDailyDigestAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_n = $recuperateur->getInt('id_n');
		$infoNotification = $this->getNotification()->getInfo($id_n);
		$id_u = $infoNotification['id_u'];
		$id_e = $infoNotification['id_e'];
		$type = $infoNotification['type'];
		
		$this->verifEditNotification($id_u, $id_e,$type);
		$this->getNotification()->toogleDailyDigest($id_u,$id_e,$type);
		$this->setLastMessage("La notification a été modifié");
		$this->redirectToPageUtilisateur($id_u);
	}
	
	public function getCertificatAction(){
		$recuperateur = new Recuperateur($_GET);
		$verif_number = $recuperateur->get('verif_number');

		$utilisateurListe = $this->getUtilisateurListe();
		
		$liste = $utilisateurListe->getUtilisateurByCertificat($verif_number,0,1);

		if (count($liste) < 1){
			header("Location: index.php");
			exit;
		}


		$certificat = new Certificat($liste[0]['certificat']);


		header("Content-type: text/plain");
		header("Content-disposition: attachment; filename=".$verif_number.".pem");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");

		echo $certificat->getContent();
	}

	public function doModifPasswordAction(){
		$recuperateur = new Recuperateur($_POST);

		$oldpassword = $recuperateur->get('old_password');
		$password = $recuperateur->get('password');
		$password2 = $recuperateur->get('password2');

		if ($password != $password2){
			$this->setLastError("Les mots de passe ne correspondent pas");
			$this->redirect("Utilisateur/modifPassword");
		}


		if ( ! $this->getUtilisateur()->verifPassword($this->getId_u(),$oldpassword)){
			$this->setLastError("Votre ancien mot de passe est incorrecte");
			$this->redirect("Utilisateur/modifPassword");
		}


		$this->getUtilisateur()->setPassword($this->getId_u(),$password);

		$this->setLastMessage("Votre mot de passe a été modifié");
		$this->redirect("/Utilisateur/moi");
	}

	public function supprimerCertificatAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_u = $recuperateur->get('id_u');

		$info = $this->getUtilisateur()->getInfo($id_u);

		$this->verifDroit($info['id_e'],"utilisateur:edition");
		
		$this->getUtilisateur()->removeCertificat($id_u);

		$this->redirect("/Utilisateur/edition?id_u=$id_u");
	}
	
}