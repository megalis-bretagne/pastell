<?php 
class MailSecControler extends PastellControler {
	
	const NB_MAIL_AFFICHE = 100;

	public function _beforeAction() {
		parent::_beforeAction();
		$id_e = $this->getPostOrGetInfo()->getInt('id_e');
		$this->{'id_e'} = $id_e;
		$this->hasDroitLecture($id_e);
		$this->setNavigationInfo($id_e,"MailSec/annuaire?");
		$this->{'menu_gauche_select'} = 'MailSec/annuaire';
		$this->{'menu_gauche_template'} = "EntiteMenuGauche";
	}

	/**
	 * @return DocumentEmail
	 */
	private function getDocumentEmail(){
		return $this->{'DocumentEmail'};
	}

	/**
	 * @return AnnuaireSQL
	 */
	private function getAnnuaireSQL(){
		return $this->{'AnnuaireSQL'};
	}

	/**
	 * @return AnnuaireRoleSQL
	 */
	private function getAnnuaireRoleSQL(){
		return $this->{'AnnuaireRoleSQL'};
	}

	public function annuaireAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		$this->{'id_g'} = $recuperateur->getInt('id_g');
		$this->{'search'} = $recuperateur->get('search','');
		$this->{'offset'} = $recuperateur->getInt('offset');
		$this->{'limit'} = self::NB_MAIL_AFFICHE;

		$this->verifDroit($id_e, "annuaire:lecture");
		
		$this->{'can_edit'} = $this->hasDroit($id_e,"annuaire:edition");


		$listUtilisateur = $this->getAnnuaireSQL()->getUtilisateurList(
			$id_e,
			$this->{'offset'},
			$this->{'limit'},
			$this->{'search'},
			$this->{'id_g'}
		);

		$this->{'nb_email'} = $this->getAnnuaireSQL()->getNbUtilisateur($id_e,$this->{'search'},$this->{'id_g'});
		
		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(), $id_e);
		
		foreach($listUtilisateur as $i => $utilisateur){
			$listUtilisateur[$i]['groupe'] = $annuaireGroupe->getGroupeFromUtilisateur($utilisateur['id_a']);  
		}
		
		$this->{'listUtilisateur'} = $listUtilisateur;
		
		$this->{'groupe_list'} = $annuaireGroupe->getGroupe();
		
		
		$this->setInfoEntite($id_e);
		$this->{'id_e'} = $id_e;
		$this->{'page'}= "Carnet d'adresses";
		$this->{'page_title'}= $this->{'infoEntite'}['denomination'] . " - Carnet d'adresses";
		$this->{'template_milieu'} = "MailSecAnnuaire";
		$this->renderDefault();
	}
	
	private function setInfoEntite($id_e){
		if ($id_e){
			$this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($id_e);
		} else  {
			$this->{'infoEntite'} = array("denomination"=>"Annuaire global");
				
		}
	}
	
	public function indexAction(){
		$recuperateur = new Recuperateur($_GET);
		$key = $recuperateur->get('key');
		
		$info  = $this->getDocumentEmail()->getInfoFromKey($key);
		if (! $info ){
			header_wrapper("Location: " . WEBSEC_BASE ."/invalid.php");
			exit_wrapper();
		}

		/** @var DocumentEntite $documentEntite */
		$documentEntite = $this->{'DocumentEntite'};

		$id_e = $documentEntite->getEntiteWithRole($info['id_d'],'editeur');
		
		$infoEntite = $this->getEntiteSQL()->getInfo($id_e);

		$document_info = $this->{'Document'}->getInfo($info['id_d']);

		$type = $document_info['type'];

		$flux_destinataire = "{$type}-destinataire";

		if (! $this->getDocumentTypeFactory()->isTypePresent($flux_destinataire)){
			$flux_destinataire = 'mailsec-destinataire';
		}

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d'],$flux_destinataire);
		
		$ip = $_SERVER['REMOTE_ADDR'];
		
		if ($donneesFormulaire->get('password') && (empty($_SESSION["consult_ok_{$key}_{$ip}"]))){
			header("Location: " . WEBSEC_BASE ."/password.php?key=$key");
			exit;
		}
		$this->getDocumentEmail()->consulter($key,$this->getJournal());

		$this->{'page_title'} = $infoEntite['denomination'] . " - Mail sécurisé";
		$this->{'template_milieu'} = "MailSecIndex";
		
		$this->{'manifest_info'} = $this->getManifestFactory()->getPastellManifest()->getInfo();
		$this->{'recuperation_fichier_url'} = "recuperation-fichier.php?key=$key";
		$this->{'id_e'} = $id_e;
		$this->{'donneesFormulaire'} = $donneesFormulaire;
		$this->{'my_role'} = "";

		$donneesFormulaire->getFormulaire()->setTabNumber(0);

		$this->{'fieldDataList'} = $donneesFormulaire->getFieldDataList($this->{'my_role'},0);

		$flux_reponse = "{$type}-reponse";
		$this->{'inject'} = array(
			'id_e'=>'',
			'id_d'=>'',
			'action'=>'',
			'id_ce'=>'',
			'key' => $key
		);

		if (($this->getDocumentTypeFactory()->isTypePresent($flux_reponse)) && ($info['type_destinataire'] == "to")){

			$donneesFormulaireReponse = $this->getDonneesFormulaireFactory()->get(0,$flux_reponse);
			$donneesFormulaireReponse->getFormulaire()->setTabNumber(0);


			$this->{'action_url'} = "reponse-edition.php";
			$this->{'page'} = 0;
			$this->{'info_reponse'} = $info['reponse'];
			$fieldDataListResponse = $donneesFormulaireReponse->getFieldDataList($this->{'my_role'}, 0);

			if ($info['reponse']) {
				$tabReponse  = json_decode($info['reponse'], true);

                foreach($tabReponse as $key => $value){
                    $tabReponse[$key] = $value;
                    $key2 = Field::Canonicalize($key);
                    if ($key2 == $key){
                        continue;
                    }
                    $tabReponse[$key2] = $tabReponse[$key];
                    unset($tabReponse[$key]);
                }

				/**
				 * @var int $i
				 * @var FieldData $fieldData
				 */
				foreach($fieldDataListResponse as $i => $fieldData){
					$fieldData->setValue($tabReponse[$fieldData->getField()->getName()]);
				}
			}

			$this->{'donneesFormulaireReponse'} = $donneesFormulaireReponse;
			$this->{'fieldDataListResponse'} = $fieldDataListResponse;
		}



		$this->render("PageWebSec");
	}

	public function reponseEditionAction(){
		$recuperateur = new Recuperateur($_POST);
		$key = $recuperateur->get('key');

		$info  = $this->getDocumentEmail()->getInfoFromKey($key);

		if (! $info ){
			header_wrapper("Location: " . WEBSEC_BASE ."/invalid.php");
			exit_wrapper();
		}
		/** @var DocumentEntite $documentEntite */
		$documentEntite = $this->{'DocumentEntite'};

		$id_e = $documentEntite->getEntiteWithRole($info['id_d'],'editeur');

		/** @var ActionExecutorFactory $actionExecutorFactory */
		$actionExecutorFactory = $this->{'ActionExecutorFactory'};

		$result = $actionExecutorFactory->executeOnDocument($id_e,-1,$info['id_d'],'modification-reponse');
		$message = $actionExecutorFactory->getLastMessage();

		if (! $result ){
			$this->getLastError()->setLastMessage($message);
		} else {
			$this->getLastMessage()->setLastMessage($message);
		}

		header_wrapper("Location: index.php?key=$key");
	}

	public function passwordAction(){
		$recuperateur = new Recuperateur($_GET);
		$key = $recuperateur->get('key');
		$info  = $this->getDocumentEmail()->getInfoFromKey($key);
		if (! $info ){
			header("Location: " . WEBSEC_BASE ."/invalid.php");
			exit;
		}
		
		$this->{'page'}= "Mail sécurisé";
		$this->{'page_title'} = " Mail sécurisé";
		$this->{'the_key'} = $key;
		$this->{'template_milieu'} = "MailSecPassword";
		$this->{'manifest_info'} = $this->getManifestFactory()->getPastellManifest()->getInfo();
		$this->render("PageWebSec");
	}
	
	public function invalidAction(){
		$this->{'page'}= "Mail sécurisé";
		$this->{'page_title'}= " Mail sécurisé";
		$this->{'template_milieu'} = "MailSecInvalid";
		$this->{'manifest_info'} = $this->getManifestFactory()->getPastellManifest()->getInfo();
		$this->render("PageWebSec");
	}
	
	public function groupeListAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		$this->verifDroit($id_e, "annuaire:lecture");
		$this->{'can_edit'} = $this->hasDroit($id_e,"annuaire:edition");
		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(),$id_e);
		$this->{'listGroupe'} = $annuaireGroupe->getGroupe();
		
		
		$infoEntite = $this->getEntiteSQL()->getInfo($id_e);
		if ($id_e == 0){
			$infoEntite = array("denomination"=>"Annuaire global");
		}
		
		$all_ancetre = $this->getEntiteSQL()->getAncetreId($id_e);
		$this->{'groupe_herited'} = $annuaireGroupe->getGroupeHerite($all_ancetre);
		$this->{'annuaireGroupe'} = $annuaireGroupe;
		$this->{'infoEntite'} = $infoEntite;
		$this->{'id_e'} = $id_e;
		$this->{'page'} = "Carnet d'adresses";
		$this->{'page_title'} = $infoEntite['denomination'] . " - Carnet d'adresses";
		$this->{'template_milieu'} = "MailSecGroupeList";
		$this->renderDefault();
	}
	
	public function groupeAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		$id_g = $recuperateur->getInt('id_g');
		$offset = $recuperateur->getInt('offset');
		$this->verifDroit($id_e, "annuaire:lecture");
		$this->{'can_edit'} = $this->hasDroit($id_e,"annuaire:edition");
		
		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(),$id_e);
		$this->{'infoGroupe'} = $annuaireGroupe->getInfo($id_g);
		$this->{'listUtilisateur'} = $annuaireGroupe->getUtilisateur($id_g,$offset);
		$this->{'nbUtilisateur'} = $annuaireGroupe->getNbUtilisateur($id_g);
		
		if ($id_e){
			$this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($id_e);
		} else{
			$this->{'infoEntite'} = array("denomination"=>"Annuaire global");
		}
		
		$this->{'id_e'} = $id_e;
		$this->{'id_g'} = $id_g;
		$this->{'offset'} = $offset;
		
		$this->{'page'} = "Carnet d'adresses";
		$this->{'page_title'} = $this->{'infoEntite'}['denomination'] . " - Carnet d'adresses";
		
		$this->{'template_milieu'} = "MailSecGroupe";
		$this->renderDefault();
	}
	
	public function groupeRoleListAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		$this->verifDroit($id_e, "annuaire:lecture");
		$this->{'can_edit'} = $this->hasDroit($id_e,"annuaire:edition");
				
		$this->{'arbre'} = $this->getRoleUtilisateur()->getArbreFille($this->getId_u(),"entite:edition");
		
		$this->{'listGroupe'} = $this->getAnnuaireRoleSQL()->getAll($id_e);
		
		if ($id_e){
			$this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($id_e);
		} else {
			$this->{'infoEntite'} = array("denomination"=>"Annuaire global");
		}
		
		$all_ancetre = $this->getEntiteSQL()->getAncetreId($id_e);
		$this->{'groupe_herited'} = $this->getAnnuaireRoleSQL()->getGroupeHerite($all_ancetre);
		$this->{'id_e'} = $id_e;
		$this->{'annuaireRole'} = $this->getAnnuaireRoleSQL();
		$this->{'page'} = "Carnet d'adresses";
		$this->{'page_title'} = $this->{'infoEntite'}['denomination'] . " - Carnet d'adresses";
		$this->{'template_milieu'} = "MailSecGroupeRoleList";
		$this->renderDefault();
	}
	
	public function importAction(){
		$recuperateur = new Recuperateur($_GET);
		$this->{'id_e'} = $recuperateur->getInt('id_e');
		$this->verifDroit($this->{'id_e'}, "annuaire:edition");
		
		$this->{'entite_info'} = $this->getEntiteSQL()->getInfo($this->{'id_e'});
		
		$this->{'page_title'} = "Importer un carnet d'adresse";
		$this->{'template_milieu'} = "MailSecImporter";
		$this->renderDefault();
	}
	
	public function doImportAction(){
		$recuperateur = new Recuperateur($_POST);
		
		$id_e = $recuperateur->getInt('id_e',0);
		$this->verifDroit($id_e, "annuaire:edition");
		
		$fileUploader = new FileUploader();
		$file_path = $fileUploader->getFilePath('csv');
		if (! $file_path){
			$this->getLastError()->setLastError("Impossible de lire le fichier");
			header("Location: import?id_e=$id_e");
			exit;
		}

		$finfo = new finfo();

        if (! in_array($finfo->file($file_path,FILEINFO_MIME_TYPE), array( 'text/plain','text/csv'))){
            $this->setLastError("Le fichier doit être en CSV");
            $this->redirect("/MailSec/import?id_e=$id_e");
        }

		$annuaireImporter = new AnnuaireImporter(
			new CSV(),
			$this->getAnnuaireSQL(),
			new AnnuaireGroupe($this->getSQLQuery(), $id_e)
		);
		$nb_import = $annuaireImporter->import($id_e,$file_path);
		
		$this->getLastMessage()->setLastMessage("$nb_import emails ont été importés");
		header("Location: annuaire?id_e=$id_e");
	}
	
	public function exportAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		
		$this->verifDroit($id_e, "annuaire:lecture");

		$annuaireExporter = new AnnuaireExporter(
			new CSVoutput(),
			$this->getAnnuaireSQL(),
			new AnnuaireGroupe($this->getSQLQuery(), $id_e)
		);
		$annuaireExporter->export($id_e);
	}
		
	public function detailAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_a = $recuperateur->getInt('id_a');
		$this->{'info'} = $this->getAnnuaireSQL()->getInfo($id_a);
		
		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(), $this->{'info'}['id_e']);
		
		$this->{'groupe_list'} = $annuaireGroupe->getGroupeFromUtilisateur($id_a);
		
		$this->verifDroit($this->{'info'}['id_e'],"annuaire:lecture");
		$this->setInfoEntite($this->{'info'}['id_e']);
		$this->{'can_edit'} = $this->hasDroit($this->{'info'}['id_e'],"annuaire:edition");
		
		
		$this->{'page_title'} = $this->{'infoEntite'}['denomination'] .
			" - Détail de l'adresse « {$this->{'info'}['email']} »";
		$this->{'template_milieu'} = "MailSecDetail";
		$this->renderDefault();
	}
	
	public function editAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_a = $recuperateur->getInt('id_a');
		$this->{'info'} = $this->getAnnuaireSQL()->getInfo($id_a);
		$this->verifDroit($this->{'info'}['id_e'],"annuaire:edition");
		$this->setInfoEntite($this->{'info'}['id_e']);
		
		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(), $this->{'info'}['id_e']);
		
		$this->{'groupe_list'} = $annuaireGroupe->getGroupeWithHasUtilisateur($id_a);
		
		$this->{'page_title'} = $this->{'infoEntite'}['denomination'] .
			" - Édition de l'adresse « {$this->{'info'}['email']} »";
		$this->{'template_milieu'} = "MailSecEdit";
		$this->renderDefault();
	}
	
	public function doEditAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_a = $recuperateur->getInt('id_a');
		$description = $recuperateur->get('description','');
		$email = $recuperateur->get('email');
		$id_g_list = $recuperateur->get('id_g');
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$this->getLastError()->setLastError("$email ne semble pas être un email valide");
			$this->redirect("MailSec/edit?id_a=$id_a");
		}
		
		$info = $this->getAnnuaireSQL()->getInfo($id_a);

		$id_a_exist = $this->getAnnuaireSQL()->getFromEmail($info['id_e'],$email);
		if($id_a_exist && ($id_a != $id_a_exist)){
			$this->getLastError()->setLastError("$email existe déjà dans l'annuaire");
			$this->redirect("MailSec/edit?id_a=$id_a");
		}
		
		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(), $info['id_e']);
		$annuaireGroupe->deleleteFromAllGroupe($id_a);

		if ($id_g_list) {
            foreach($id_g_list as $id_g){
                $annuaireGroupe->addToGroupe($id_g, $id_a);
            }
        }

		$this->verifDroit($info['id_e'],"annuaire:edition");
		$this->getAnnuaireSQL()->edit($id_a,$description,$email);
		$this->getLastMessage()->setLastMessage("Le contact a été modifié");
		$this->redirect("MailSec/detail?id_a=$id_a&id_e=".$info['id_e']);
	}
	
	public function deleteAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$id_a_list = $recuperateur->getInt('id_a');
				
		if (! $id_a_list){
			$this->getLastError()->setLastError("Vous devez sélectionner au moins un email à supprimer");
			$this->redirect("MailSec/annuaire?id_e=$id_e");
		}
		$this->verifDroit($id_e, "annuaire:edition");
		
		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(), $id_e);
		
		if (! is_array($id_a_list)){
			$id_a_list = array($id_a_list);
		}
		
		foreach ($id_a_list as $id_a){
			$annuaireGroupe->deleteAllGroupFromContact($id_a);
			$this->getAnnuaireSQL()->delete($id_e,$id_a);
		}
		$this->getLastMessage()->setLastMessage("Email(s) supprimé(s) de la liste de contacts");
		$this->redirect("MailSec/annuaire?id_e=$id_e");
	}

	public function addContactAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$description = $recuperateur->get('description',"");
		$email = $recuperateur->get('email');

		$this->verifDroit($id_e,"annuaire:edition","MailSec/annuaire?id_e=$id_e");


		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$this->setLastError("$email ne semble pas être un email valide");
			$this->redirect("MailSec/annuaire?id_e=$id_e");
		}

		if($this->getAnnuaireSQL()->getFromEmail($id_e,$email)){
			$this->setLastError("$email existe déjà dans l'annuaire");
			$this->redirect("MailSec/annuaire?id_e=$id_e");
		}

		$this->getAnnuaireSQL()->add($id_e,$description,$email);

		$mail = htmlentities("\"$description\"<$email>",ENT_QUOTES);

		$this->setLastMessage("$mail a été ajouté à la liste de contacts");
		$this->redirect("MailSec/annuaire?id_e=$id_e");
	}

	public function addContactToGroupeAction(){

		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$name = $recuperateur->get('name');
		$id_g = $recuperateur->get('id_g');

		$this->verifDroit($id_e,"annuaire:edition","MailSec/annuaire?id_e=$id_e");

		$id_a = false;
		$email = "";
		if (preg_match("/<([^>]*)>/u",$name,$matches)) {
			$email = $matches[1];
			$id_a = $this->getAnnuaireSQL()->getFromEmail($id_e, $email);
		}

		if (! $id_a){
			$this->setLastError("L'email $email est inconnu");
			$this->redirect("MailSec/groupe?id_e=$id_e&id_g=$id_g");
		}

		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(),$id_e);
		$annuaireGroupe->addToGroupe($id_g,$id_a);

		$mail = htmlentities($name,ENT_QUOTES);

		$this->setLastMessage("$mail a été ajouté à ce groupe");
		$this->redirect("MailSec/groupe?id_e=$id_e&id_g=$id_g");
	}
	
	public function addGroupeAction(){

		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$nom = $recuperateur->get('nom');
		if (! $nom){
            $this->redirect("MailSec/groupeList?id_e=$id_e");
        }

		$this->verifDroit($id_e,"annuaire:edition","MailSec/annuaire?id_e=$id_e");

		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(),$id_e);

		$annuaireGroupe->add($nom);

		$this->setLastMessage("Le groupe « $nom » a été créé");
		$this->redirect("MailSec/groupeList?id_e=$id_e");
	}

	public function addGroupeRoleAction(){

		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$id_e_owner = $recuperateur->getInt('id_e_owner');
		$role = $recuperateur->get('role');

		$this->verifDroit($id_e,"annuaire:edition","MailSec/annuaire?id_e=$id_e");
		$this->verifDroit($id_e_owner,"annuaire:edition","MailSec/annuaire?id_e=$id_e");


		$infoEntite = $this->getEntiteSQL()->getInfo($id_e);

		if ($id_e != 0){
			$nom = "$role - {$infoEntite['denomination']}";
		} else {
			$nom = "$role - toutes les collectivités";
		}

		$this->getAnnuaireRoleSQL()->add($nom,$id_e_owner,$id_e,$role);

		$this->setLastMessage("Le groupe « $nom » a été créé");
		$this->redirect("MailSec/groupeRoleList?id_e=$id_e_owner");
	}

	public function delContactFromGroupeAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$id_g = $recuperateur->get('id_g');
		$id_a = $recuperateur->get('id_a');
		$this->verifDroit($id_e,"annuaire:edition","MailSec/annuaire?id_e=$id_e");

		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(),$id_e);
		$annuaireGroupe->deleteFromGroupe($id_g,$id_a);

		$this->setLastMessage("Email retiré du groupe");
		$this->redirect("MailSec/groupe?id_e=$id_e&id_g=$id_g");
	}

	public function delGroupeAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$id_g = $recuperateur->get('id_g',array());

		$this->verifDroit($id_e,"annuaire:edition","MailSec/annuaire?id_e=$id_e");


		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(),$id_e);

		$annuaireGroupe->delete($id_g);

		if ($id_g) {
			$this->setLastMessage("Les groupes sélectionnés ont été supprimés");
		}

		$this->redirect("MailSec/groupeList?id_e=$id_e");
	}


	public function getContactAjaxAction(){
		$recuperateur = new Recuperateur($_REQUEST);
		$id_e = $recuperateur->getInt('id_e');
		$q = $recuperateur->get('term');
		$mailOnly = $recuperateur->get('mail-only');

		$this->verifDroit($id_e,"annuaire:lecture");

		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(),$id_e);

		$result = array();

		$all_ancetre = $this->getEntiteSQL()->getAncetreId($id_e);

		$groupe_herited = $annuaireGroupe->getGroupeHerite($all_ancetre,$q);
		$role_herited = $this->getAnnuaireRoleSQL()->getGroupeHerite($all_ancetre,$q);

		if ($mailOnly == "false"){

			foreach($annuaireGroupe->getListGroupe($q) as $item){
				$result[] = "groupe: \"".$item['nom'] ."\"\n";
			}
			foreach($this->getAnnuaireRoleSQL()->getList($id_e,$q) as $item){
				$result[] = "role: \"".$item['nom'] ."\"\n";
			}
			foreach($groupe_herited as $item){
				$result[] = $annuaireGroupe->getChaineHerited($item)."\n";
			}
			foreach($role_herited as $item){
				$result[] = $this->getAnnuaireRoleSQL()->getChaineHerited($item)."\n";
			}

		}


		foreach ($this->getAnnuaireSQL()->getListeMail($id_e,$q) as $item){
			$result[] = '"'.$item['description'] . '"'." <".$item['email'].">";
		}

		foreach($result as $i => $line){
			$result[$i] = $line;
		}

		echo json_encode($result);

	}

	public function operationGroupeRoleAction(){
		$recuperateur = new Recuperateur($_POST);
		$all_id_r = $recuperateur->get('id_r',array());
		$id_e = $recuperateur->getInt('id_e');
		$submit = $recuperateur->get('submit');

		foreach($all_id_r as $id_r) {
			$info = $this->getAnnuaireRoleSQL()->getInfo($id_r);

			if ( $this->getRoleUtilisateur()->hasDroit($this->getId_u(),"annuaire:edition",$info['id_e_owner'])) {
				if ($submit == "Supprimer"){
					$this->getAnnuaireRoleSQL()->delete($id_r);
					$this->setLastMessage("Les groupes sélectionnés ont été supprimés");
				} elseif($submit == "Partager"){
					$this->getAnnuaireRoleSQL()->partage($id_r);
					$this->setLastMessage("Les groupes sélectionnés sont accessibles aux entités filles");
				} else {
					$this->getAnnuaireRoleSQL()->unpartage($id_r);
					$this->setLastMessage("Les groupes sélectionnés ne sont plus accessibles aux entités filles");
				}
			}
		}
		$this->redirect("MailSec/groupeRoleList?id_e=$id_e");
	}

	public function partageGroupeAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$id_g = $recuperateur->get('id_g');

		$this->verifDroit($id_e,"annuaire:edition","MailSec/annuaire?id_e=$id_e");


		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(),$id_e);
		$annuaireGroupe->tooglePartage($id_g);
		$info = $annuaireGroupe->getInfo($id_g);
		if ($info['partage']){
			$this->setLastMessage("Le groupe est maintenant partagé");
		} else {
			$this->setLastMessage("Le partage du groupe a été supprimé");
		}
		$this->redirect("MailSec/groupe?id_e=$id_e&id_g=$id_g");
	}

}