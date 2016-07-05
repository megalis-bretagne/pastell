<?php
class UtilisateurControler extends PastellControler {

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
		if ( ! $this->getUtilisateur()->verifPassword($this->Authentification->getId(),$password)){
			$this->{'LastError'}->setLastError("Le mot de passe est incorrect.");
			$this->redirect("/Utilisateur/modifEmail");
		}
		$email = $recuperateur->get('email');
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$this->{'LastError'}->setLastError("L'email que vous avez saisi ne semble pas être valide");
			$this->redirect("/Utilisateur/modifEmail");
		}
		
		$utilisateur_info = $this->Utilisateur->getInfo($this->Authentification->getId()); 
		
		
		$password = $this->UtilisateurNewEmailSQL->add($this->Authentification->getId(),$email);
		
		$zenMail = $this->ZenMail;
		$zenMail->setEmetteur("Pastell",PLATEFORME_MAIL);
		$zenMail->setDestinataire($email);
		$zenMail->setSujet("Changement de mail sur Pastell");
		$info = array("password" => $password);
		$zenMail->setContenu(PASTELL_PATH . "/mail/changement-email.php",$info);
		$zenMail->send();
		
		$this->Journal->add(Journal::MODIFICATION_UTILISATEUR,$utilisateur_info['id_e'],0,"change-email","Demande de changement d'email initiée {$utilisateur_info['email']} -> $email");

		$this->LastMessage->setLastMessage("Un email a été envoyé à votre nouvelle adresse. Merci de le consulter pour la suite de la procédure.");
		$this->redirect("/Utilisateur/moi");
	}
	
	public function modifEmailConfirmAction(){
		$recuperateur = new Recuperateur($_GET);
		$password = $recuperateur->get('password');
		$info = $this->UtilisateurNewEmailSQL->confirm($password);
		if ($info){
			$this->createChangementEmail($info['id_u'],$info['email']);
		}

		$this->UtilisateurNewEmailSQL->delete($info['id_u']);
		$this->result = $info;
		$this->page_title = "Procédure de changement d'email";
		$this->template_milieu = "UtilisateurModifEmailConfirm";
		$this->renderDefault();
	}
	
	private function createChangementEmail($id_u,$email){
		$id_d = $this->Document->getNewId();	
		$this->Document->save($id_d,'changement-email');
		$utilisateur_info = $this->Utilisateur->getInfo($id_u); 
		
		$this->Document->setTitre($id_d,$utilisateur_info['login']);
		$this->DocumentEntite->addRole($id_d,$utilisateur_info['id_e'],"editeur");
		$actionCreator = new ActionCreator($this->SQLQuery,$this->Journal,$id_d);
		$actionCreator->addAction($utilisateur_info['id_e'],$id_u,Action::CREATION,"Création du document");
		
		$donneesFormulaire = $this->DonneesFormulaireFactory->get($id_d);
		foreach(array('id_u','login','nom','prenom') as $key){
			$data[$key] = $utilisateur_info[$key];
		}
		$data['email_actuel'] = $utilisateur_info['email'];
		$data['email_demande'] = $email;
		$donneesFormulaire->setTabData($data);
		
		$this->NotificationMail->notify($utilisateur_info['id_e'],$id_d,'creation','changement-email',$utilisateur_info['login']." a fait une demande de changement d'email");
	}
	
	public function certificatAction(){
		$recuperateur = new Recuperateur($_GET);
		$this->verif_number = $recuperateur->get('verif_number');
		$this->offset = $recuperateur->getInt('offset',0);
	
		$this->limit = 20;
		
		$this->count = $this->UtilisateurListe->getNbUtilisateurByCertificat($this->verif_number);
		$this->liste = $this->UtilisateurListe->getUtilisateurByCertificat($this->verif_number,$this->offset,$this->limit);
		
		if (! $this->count){
			$this->redirect("/index.php");
		}
		
		$this->certificat = new Certificat($this->liste[0]['certificat']);
		$this->certificatInfo = $this->certificat->getInfo();
		
		$this->page_title = "Certificat";
		$this->template_milieu = "UtilisateurCertificat";
		$this->renderDefault();
	}
	
	public function editionAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_u = $recuperateur->get('id_u');
		$id_e = $recuperateur->getInt('id_e');
		
		$infoUtilisateur = array('login' =>  $this->LastError->getLastInput('login'),
							'nom' =>  $this->LastError->getLastInput('nom'),
							'prenom' =>  $this->LastError->getLastInput('prenom'),
							'email'=> $this->LastError->getLastInput('email'),
							'certificat' => '',
							'id_e' => $id_e,
		);
		
		if ($id_u){
			$infoUtilisateur = $this->Utilisateur->getInfo($id_u);
			if (! $infoUtilisateur){
				$this->redirect();
			}
		}
		
		$this->verifDroit($infoUtilisateur['id_e'], "utilisateur:edition");

		$this->infoEntite = $this->EntiteSQL->getInfo($infoUtilisateur['id_e']);
		$this->certificat = new Certificat($infoUtilisateur['certificat']);
		$this->arbre = $this->RoleUtilisateur->getArbreFille($this->getId_u(),"entite:edition");
		
		if ($id_u){
			$this->page_title = "Modification de " .  $infoUtilisateur['prenom']." ". $infoUtilisateur['nom'];
		} else {
			$this->page_title = "Nouvel utilisateur ";	
		}
		$this->id_u = $id_u;
		$this->id_e = $id_e;
		$this->infoUtilisateur = $infoUtilisateur;
		$this->template_milieu = "UtilisateurEdition";
		$this->renderDefault();
	}
	
	public function detailAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_u = $recuperateur->get('id_u');
		
		$info = $this->Utilisateur->getInfo($id_u);
		if (! $info){
			$this->LastError->setLastError("Utilisateur $id_u inconnu");
			$this->redirect("index.php");
		}
		
		$this->certificat = new Certificat($info['certificat']);
		$this->page_title = "Utilisateur ".$info['prenom']." " . $info['nom'];
		$this->entiteListe = $this->EntiteListe;
		$this->tabEntite = $this->RoleUtilisateur->getEntite($this->getId_u(),'entite:edition');
		
		$this->notification_list = $this->getNotificationList($id_u);
		
		$roleInfo =  $this->RoleUtilisateur->getRole($id_u);
		
		
		if ( ! $this->RoleUtilisateur->hasDroit($this->getId_u(),"utilisateur:lecture",$info['id_e'])) {
			$this->LastError->setLastError("Vous n'avez pas le droit de lecture (".$info['id_e'].")");
			$this->redirect();
		}
		$this->utilisateur_edition = $this->RoleUtilisateur->hasDroit($this->getId_u(),"utilisateur:edition",$info['id_e']);
		
		if( $info['id_e'] ){
			$this->infoEntiteDeBase = $this->EntiteSQL->getInfo($info['id_e']);
			$this->denominationEntiteDeBase = $this->infoEntiteDeBase['denomination'];
		}
		$this->info = $info;
		$this->id_u = $id_u;
		$this->arbre = $this->RoleUtilisateur->getArbreFille($this->getId_u(),"entite:edition");
		$this->template_milieu = "UtilisateurDetail";
		$this->renderDefault();
	}
		
	private function getNotificationList($id_u){
		$result  = $this->Notification->getAll($id_u);
		foreach($result as $i => $line){
			$action  = $this->DocumentTypeFactory->getFluxDocumentType($line['type'])->getAction();
			foreach($line['action'] as $j => $action_id){
				$result[$i]['action'][$j] = $action->getActionName($action_id);
			}
		}
		return $result;
	}
	
	public function moiAction(){
		$id_u = $this->getId_u();
		$info = $this->Utilisateur->getInfo($id_u);
		$this->certificat = new Certificat($info['certificat']);
		
		$this->page_title = "Espace utilisateur : ".$info['prenom']." " . $info['nom'];
		
		$this->entiteListe = $this->EntiteListe;
		
		$this->tabEntite = $this->RoleUtilisateur->getEntite($this->getId_u(),'entite:edition');
		
		$this->notification_list = $this->getNotificationList($id_u);
		
		$this->roleInfo =  $this->RoleUtilisateur->getRole($id_u);
		$this->utilisateur_edition = $this->RoleUtilisateur->hasDroit($this->getId_u(),"utilisateur:edition",$info['id_e']);
		
		if( $info['id_e'] ){
			$infoEntiteDeBase = $this->EntiteSQL->getInfo($info['id_e']);
			$this->denominationEntiteDeBase = $infoEntiteDeBase['denomination'];
		}
		$this->info = $info;
		$this->id_u = $id_u;
		$this->arbre = $this->RoleUtilisateur->getArbreFille($this->getId_u(),"entite:lecture");
		$this->template_milieu = "UtilisateurMoi";
		$this->renderDefault();
	}
	
        
        // Prise en compte du paramètre $message dans l'affectation de l'erreur
        // Correction "lastError"        
	private function redirectEdition($id_e,$id_u,$message){
		$this->LastError->setLastError($message);
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
		if ($this->RoleUtilisateur->hasRole($id_u,$role,$id_e)){
			$this->LastError->setLastError("Ce droit a déjà été attribué à l'utilisateur");
		} elseif ($role){
			$this->RoleUtilisateur->addRole($id_u,$role,$id_e);	
		}
		$this->redirect("/Utilisateur/detail?id_u=$id_u");
	}
	
	public function supprimeRoleAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_u = $recuperateur->get('id_u');
		$role = $recuperateur->get('role');
		$id_e = $recuperateur->getInt('id_e',0);
		$this->verifDroit($id_e,"entite:edition");
		$this->RoleUtilisateur->removeRole($id_u,$role,$id_e);
		$this->redirect("/Utilisateur/detail?id_u=$id_u");
	}
	
	private function verifEditNotification($id_u,$id_e,$type){
		$utilisateur_info = $this->Utilisateur->getInfo($id_u);
	
		if (
				$this->RoleUtilisateur->hasDroit($this->getId_u(),"entite:edition",$id_e)
				&&
				$this->RoleUtilisateur->hasDroit($this->getId_u(),"utilisateur:edition",$utilisateur_info['id_e'])
		){
			return true;
		}
	
		if (
				$id_u == $this->getId_u()
				&&
				$this->RoleUtilisateur->hasDroit($this->getId_u(),"entite:lecture",$id_e)
				&&
				$this->RoleUtilisateur->hasDroit($this->getId_u(),"$type:lecture",$id_e)
		){
			return true;
		}
	
		$this->LastError->setLastError("Vous n'avez pas les droits nécessaires pour faire cela");
		$this->redirectToPageUtilisateur($id_u);
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
		$this->Notification->add($id_u,$id_e,$type,0,$daily_digest);
		$this->LastMessage->setLastMessage("La notification a été ajoutée");
		$this->redirectToPageUtilisateur($id_u);
	}
	
	public function notificationAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_u = $recuperateur->getInt('id_u');
		$id_e = $recuperateur->getInt('id_e');
		$type = $recuperateur->get('type');

		$utilisateur_info = $this->Utilisateur->getInfo($id_u);
		$this->verifEditNotification($id_u, $id_e,$type);
		
		$documentType = $this->DocumentTypeFactory->getFluxDocumentType($type);
		$titreSelectAction = $type ? "Sélectionner les actions des documents de type ".$type:"La sélection des actions n'est pas possible car aucun type de document n'est spécifié";
		
		$action_list = $documentType->getAction()->getActionWithNotificationPossible();

		$this->titreSelectAction = $titreSelectAction;
		$this->action_list = $this->Notification->getNotificationActionList($id_u,$id_e,$type,$action_list);
		$this->id_u = $id_u;
		$this->id_e = $id_e;
		$this->type = $type;
		
		
		$this->page_title = get_hecho($utilisateur_info['login'])." - abonnement aux actions des documents " ;
		$this->template_milieu = "UtilisateurNotification";
		$this->renderDefault();
	}
	
	public function notificationSuppressionAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_n = $recuperateur->get('id_n');
		
		$infoNotification = $this->Notification->getInfo($id_n);
		$id_u = $infoNotification['id_u'];
		$id_e = $infoNotification['id_e'];
		$type = $infoNotification['type'];
		
		$this->verifEditNotification($id_u, $id_e,$type);
		$this->Notification->remove($id_n);
		$this->LastMessage->setLastMessage("La notification a été supprimée");
		$this->redirectToPageUtilisateur($id_u);
	}
	
	public function doNotificationEditAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_u = $recuperateur->getInt('id_u');
		$id_e = $recuperateur->getInt('id_e');
		$type = $recuperateur->get('type');
		
		$utilisateur_info = $this->Utilisateur->getInfo($id_u);
		$this->verifEditNotification($id_u, $id_e,$type);
		
		$documentType = $this->DocumentTypeFactory->getFluxDocumentType($type);
		
		$action_list = $documentType->getAction()->getActionWithNotificationPossible();
		
		$all_checked = true;
		$no_checked = false;
		foreach($action_list as $action){
			$checked = !! $recuperateur->get($action['id']);
			$action_checked[$action['id']] = $checked;
			$all_checked = $all_checked && $checked;
			$no_checked = $no_checked || $checked; 			
		}
		
		$daily_digest = $this->Notification->hasDailyDigest($id_u,$id_e,$type);
		
		$this->Notification->removeAll($id_u,$id_e,$type);
		
		$this->LastMessage->setLastMessage("Les notifications ont été modifiées");
		if (! $no_checked){
			$this->redirectToPageUtilisateur($id_u);
		}
		if ($all_checked){
			$this->Notification->add($id_u,$id_e,$type,Notification::ALL_TYPE,$daily_digest);
			$this->redirectToPageUtilisateur($id_u);
		}
		foreach($action_list as $action){
			if (! $action_checked[$action['id']]){
				continue;
			}
			$this->Notification->add($id_u,$id_e,$type,$action['id'],$daily_digest);
		}
		$this->redirectToPageUtilisateur($id_u);
	}
	
	public function notificationToogleDailyDigestAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_n = $recuperateur->getInt('id_n');
		$infoNotification = $this->Notification->getInfo($id_n);
		$id_u = $infoNotification['id_u'];
		$id_e = $infoNotification['id_e'];
		$type = $infoNotification['type'];
		
		$this->verifEditNotification($id_u, $id_e,$type);
		$this->Notification->toogleDailyDigest($id_u,$id_e,$type);
		$this->LastMessage->setLastMessage("La notification a été modifié");
		$this->redirectToPageUtilisateur($id_u);
	}
	
}