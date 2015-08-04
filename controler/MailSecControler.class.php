<?php 
class MailSecControler extends PastellControler {
	
	public function annuaireAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		$this->verifDroit($id_e, "annuaire:lecture");
		
		$this->can_edit = $this->hasDroit($id_e,"annuaire:edition");
		
		$annuaire = new AnnuaireSQL($this->SQLQuery);
		
		$this->listUtilisateur = $annuaire->getUtilisateur($id_e);
		
		$this->setInfoEntite($id_e);
		$this->id_e = $id_e;
		$this->page= "Carnet d'adresses";
		$this->page_title= $this->infoEntite['denomination'] . " - Carnet d'adresses";
		$this->template_milieu = "MailSecAnnuaire";
		$this->renderDefault();
	}
	
	private function setInfoEntite($id_e){
		if ($id_e){
			$this->infoEntite = $this->EntiteSQL->getInfo($id_e);
		} else  {
			$this->infoEntite = array("denomination"=>"Annuaire global");
				
		}
	}
	
	public function indexAction(){
		$recuperateur = new Recuperateur($_GET);
		$key = $recuperateur->get('key');
		
		$info  = $this->DocumentEmail->getInfoFromKey($key);
		if (! $info ){
			header("Location: " . WEBSEC_BASE ."/invalid.php");
			exit;
		}
		
		$id_e = $this->DocumentEntite->getEntiteWithRole($info['id_d'],'editeur');
		
		$infoEntite = $this->EntiteSQL->getInfo($id_e);
		
		$documentType = $this->DocumentTypeFactory->getFluxDocumentType('mailsec-destinataire');
		$donneesFormulaire = $this->DonneesFormulaireFactory->get($info['id_d'],'mailsec-destinataire');
		
		$ip = $_SERVER['REMOTE_ADDR'];
		
		if ($donneesFormulaire->get('password') && (empty($_SESSION["consult_ok_{$key}_{$ip}"]))){
			header("Location: " . WEBSEC_BASE ."/password.php?key=$key");
			exit;
		}
		$info  = $this->DocumentEmail->consulter($key,$this->Journal);

	
		$this->page= "Mail sécurisé";
		$this->page_title= $infoEntite['denomination'] . " - Mail sécurisé";
		$this->template_milieu = "MailSecIndex";
		
		$this->manifest_info = $this->ManifestFactory->getPastellManifest()->getInfo();
		$this->recuperation_fichier_url = "recuperation-fichier.php?key=$key";
		$this->id_e = $id_e;
		$this->donneesFormulaire = $donneesFormulaire;
		$this->my_role = "";
		
		$this->fieldDataList = $this->donneesFormulaire->getFieldDataListAllOnglet($this->my_role);
		
		
		$this->render("PageWebSec");
	}
	
	public function passwordAction(){
		$recuperateur = new Recuperateur($_GET);
		$key = $recuperateur->get('key');
		$info  = $this->DocumentEmail->getInfoFromKey($key);
		if (! $info ){
			header("Location: " . WEBSEC_BASE ."/invalid.php");
			exit;
		}
		
		$this->page= "Mail sécurisé";
		$this->page_title= " Mail sécurisé";
		$this->the_key = $key;
		$this->template_milieu = "MailSecPassword";
		$this->render("PageWebSec");
	}
	
	public function invalidAction(){
		$this->page= "Mail sécurisé";
		$this->page_title= " Mail sécurisé";
		$this->template_milieu = "MailSecInvalid";
		$this->render("PageWebSec");
	}
	
	public function groupeListAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		$this->verifDroit($id_e, "annuaire:lecture");
		$this->can_edit = $this->hasDroit($id_e,"annuaire:edition");
		$annuaireGroupe = new AnnuaireGroupe($this->SQLQuery,$id_e);
		$this->listGroupe = $annuaireGroupe->getGroupe();
		
		
		$infoEntite = $this->EntiteSQL->getInfo($id_e);
		if ($id_e == 0){
			$infoEntite = array("denomination"=>"Annuaire global");
		}
		
		$all_ancetre = $this->EntiteSQL->getAncetreId($id_e);
		$this->groupe_herited = $annuaireGroupe->getGroupeHerite($all_ancetre);
		$this->annuaireGroupe = $annuaireGroupe;
		$this->infoEntite = $infoEntite;
		$this->id_e = $id_e;
		$this->page= "Carnet d'adresses";
		$this->page_title= $infoEntite['denomination'] . " - Carnet d'adresses";
		$this->template_milieu = "MailSecGroupeList";
		$this->renderDefault();
	}
	
	public function groupeAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		$id_g = $recuperateur->getInt('id_g');
		$offset = $recuperateur->getInt('offset');
		$this->verifDroit($id_e, "annuaire:lecture");
		$this->can_edit = $this->hasDroit($id_e,"annuaire:edition");
		
		$annuaireGroupe = new AnnuaireGroupe($this->SQLQuery,$id_e);
		$this->infoGroupe = $annuaireGroupe->getInfo($id_g);
		$this->listUtilisateur = $annuaireGroupe->getUtilisateur($id_g,$offset);
		$this->nbUtilisateur = $annuaireGroupe->getNbUtilisateur($id_g);
		
		if ($id_e){
			$this->infoEntite = $this->EntiteSQL->getInfo($id_e);
		} else{
			$this->infoEntite = array("denomination"=>"Annuaire global");
		}
		
		$this->id_e = $id_e;
		$this->id_g = $id_g;
		$this->offset = $offset;
		
		$this->page= "Carnet d'adresses";
		$this->page_title= $this->infoEntite['denomination'] . " - Carnet d'adresses";
		
		$this->template_milieu = "MailSecGroupe";
		$this->renderDefault();
	}
	
	public function groupeRoleListAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		$this->verifDroit($id_e, "annuaire:lecture");
		$this->can_edit = $this->hasDroit($id_e,"annuaire:edition");
				
		$this->arbre = $this->RoleUtilisateur->getArbreFille($this->getId_u(),"entite:edition");
		
		$this->listGroupe = $this->AnnuaireRoleSQL->getAll($id_e);
		
		if ($id_e){
			$this->infoEntite = $this->EntiteSQL->getInfo($id_e);
		} else {
			$this->infoEntite = array("denomination"=>"Annuaire global");
		}
		
		$all_ancetre = $this->EntiteSQL->getAncetreId($id_e);
		$this->groupe_herited = $this->AnnuaireRoleSQL->getGroupeHerite($all_ancetre);
		$this->id_e = $id_e;
		$this->annuaireRole = $this->AnnuaireRoleSQL;
		$this->page= "Carnet d'adresses";
		$this->page_title= $this->infoEntite['denomination'] . " - Carnet d'adresses";
		$this->template_milieu = "MailSecGroupeRoleList";
		$this->renderDefault();
	}
	
	public function importAction(){
		$recuperateur = new Recuperateur($_GET);
		$this->id_e = $recuperateur->getInt('id_e');
		$this->verifDroit($this->id_e, "annuaire:edition");
		
		$this->entite_info = $this->EntiteSQL->getInfo($this->id_e);
		
		$this->page_title = "Importer un carnet d'adresse";
		$this->template_milieu = "MailSecImporter";
		$this->renderDefault();
	}
	
	public function doImportAction(){
		$recuperateur = new Recuperateur($_POST);
		
		$id_e = $recuperateur->getInt('id_e',0);
		$this->verifDroit($id_e, "annuaire:edition");
		
		$fileUploader = new FileUploader();
		$file_path = $fileUploader->getFilePath('csv');
		if (! $file_path){
			$this->LastError->setLastError("Impossible de lire le fichier");
			header("Location: import.php?id_e=$id_e");
			exit;
		}
		
		$annuaireImporter = new AnnuaireImporter(new CSV(), new AnnuaireSQL($this->SQLQuery), new AnnuaireGroupe($this->SQLQuery, $id_e));
		$nb_import = $annuaireImporter->import($id_e,$file_path);
		
		$this->LastMessage->setLastMessage("$nb_import emails ont été importés");
		header("Location: annuaire.php?id_e=$id_e");
	}
	
	public function exportAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		
		$this->verifDroit($id_e, "annuaire:lecture");
		
		
		$annuaireExporter = new AnnuaireExporter(new CSVoutput(), new AnnuaireSQL($this->SQLQuery), new AnnuaireGroupe($this->SQLQuery, $id_e));
		$annuaireExporter->export($id_e);
	}
		
	public function detailAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_a = $recuperateur->getInt('id_a');
		$this->info = $this->AnnuaireSQL->getInfo($id_a);
		
		$annuaireGroupe = new AnnuaireGroupe($this->SQLQuery, $this->info['id_e']);
		
		$this->groupe_list = $annuaireGroupe->getGroupeFromUtilisateur($id_a);
		
		$this->verifDroit($this->info['id_e'],"annuaire:lecture");
		$this->setInfoEntite($this->info['id_e']);
		$this->can_edit = $this->hasDroit($this->info['id_e'],"annuaire:edition");
		
		
		$this->page_title = $this->infoEntite['denomination'] .  " - Détail de l'adresse « {$this->info['email']} »";
		$this->template_milieu = "MailSecDetail";
		$this->renderDefault();
	}
	
	public function editAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_a = $recuperateur->getInt('id_a');
		$this->info = $this->AnnuaireSQL->getInfo($id_a);
		$this->verifDroit($this->info['id_e'],"annuaire:edition");
		$this->setInfoEntite($this->info['id_e']);
		$this->page_title = $this->infoEntite['denomination'] .  " - Édition de l'adresse « {$this->info['email']} »";
		$this->template_milieu = "MailSecEdit";
		$this->renderDefault();
	}
	
	public function doEditAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_a = $recuperateur->getInt('id_a');
		$description = $recuperateur->get('description','');
		$email = $recuperateur->get('email');

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$this->LastError->setLastError("$email ne semble pas être un email valide");
			$this->redirect("mailsec/edit.php?id_a=$id_a");
		}
		
		$info = $this->AnnuaireSQL->getInfo($id_a);
		
		$id_a_exist = $this->AnnuaireSQL->getFromEmail($info['id_e'],$email);
		if($id_a_exist && ($id_a != $id_a_exist)){
			$this->LastError->setLastError("$email existe déjà dans l'annuaire");
			$this->redirect("mailsec/edit.php?id_a=$id_a");
		}
		
		$this->verifDroit($info['id_e'],"annuaire:edition");
		$this->AnnuaireSQL->edit($id_a,$description,$email);
		$this->LastMessage->setLastMessage("L'email a été modifié");
		$this->redirect("mailsec/detail.php?id_a=$id_a");
	}
	
	public function deleteAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$id_a_list = $recuperateur->getInt('id_a');
				
		if (! $id_a_list){
			$this->LastError->setLastError("Vous devez sélectionner au moins un email à supprimer");
			$this->redirect("mailsec/annuaire.php?id_e=$id_e");
		}
		$this->verifDroit($id_e, "annuaire:edition");
		
		$annuaireGroupe = new AnnuaireGroupe($this->SQLQuery, $id_e);
		
		if (! is_array($id_a_list)){
			$id_a_list = array($id_a_list);
		}
		
		foreach ($id_a_list as $id_a){
			$annuaireGroupe->deleteAllGroupFromContact($id_a);
			$this->AnnuaireSQL->delete($id_e,$id_a);
		}
		$this->LastMessage->setLastMessage("Email(s) supprimé(s) de la liste de contacts");
		$this->redirect("mailsec/annuaire.php?id_e=$id_e");
	}
	
}