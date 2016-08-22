<?php

class DocumentControler extends PastellControler {

	public function _beforeAction() {
		parent::_beforeAction();
		$id_e = $this->getGetInfo()->getInt('id_e');
		$id_d = $this->getGetInfo()->get('id_d');
		$type = $this->getGetInfo()->get('type');


		if ($id_d){
			$document_info = $this->getDocument()->getInfo($id_d);
			$type = $document_info['type'];
			$this->{'type_e_menu'} = $type;
		}


		$this->setNavigationInfo($id_e,"Document/list?type=$type");

	}

	/**
	 * @return DocumentActionEntite
	 */
	protected function getDocumentActionEntite(){
		return $this->getInstance('DocumentActionEntite');
	}

	private function redirectToList($id_e,$type = false){
		$this->redirect("/Document/list?id_e=$id_e&type=$type");
	}
	
	private function verifDroitLecture($id_e,$id_d){
		$info = $this->getDocument()->getInfo($id_d);
		if (!$info){
			$this->redirectToList($id_e);
		}

		if ( ! $this->getRoleUtilisateur()->hasDroit($this->getId_u(),$info['type'].":lecture",$id_e)) {
			$this->redirectToList($id_e,$info['type']);
		}
		
		$my_role = $this->getDocumentEntite()->getRole($id_e,$id_d);
		if (! $my_role ){
			$this->redirectToList($id_e,$info['type']);
		}
		return $info;
	}
	
	public function arAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_d = $recuperateur->get('id_d');
		$id_e = $recuperateur->getInt('id_e');
		
		$info_document = $this->verifDroitLecture($id_e, $id_d);
		

		$true_last_action = $this->getDocumentActionEntite()->getTrueAction($id_e, $id_d);
		/** @var DocumentType $documentType */
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($info_document['type']);
		
 		$action = $documentType->getAction();
		if (! $action->getProperties($true_last_action,'accuse_de_reception_action')){
			$this->redirect("/Document/detail?id_e=$id_e&id_d=$id_d");
		}
		$this->{'action'} = $action->getProperties($true_last_action,'accuse_de_reception_action');
		$this->{'id_e'} = $id_e;
		$this->{'id_d'} = $id_d;
		
		$this->{'page_title'} = "Accusé de réception";
		$this->{'template_milieu'} = "DocumentAR";
		$this->renderDefault();
		
	}
	 
	public function detailAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_d = $recuperateur->get('id_d');
		$id_e = $recuperateur->getInt('id_e');
		$page = $recuperateur->getInt('page',0);

		$info_document = $this->verifDroitLecture($id_e, $id_d);

		/** @var DocumentType $documentType */
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($info_document['type']);
		
		$true_last_action = $this->getDocumentActionEntite()->getTrueAction($id_e, $id_d);
		
 		$action = $documentType->getAction();
		if ($action->getProperties($true_last_action,'accuse_de_reception_action')){
			$this->redirect("/Document/ar?id_e=$id_e&id_d=$id_d");
		}
		
		$this->getJournal()->addConsultation($id_e,$id_d,$this->getId_u());
		
		$this->{'info'} = $info_document;
		$this->{'id_e'} = $id_e;
		$this->{'id_d'} = $id_d;
		$this->{'page'} = $page;
		$this->{'documentType'} = $documentType;
		$this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($id_e);
		$this->{'formulaire'} =  $documentType->getFormulaire();
		$this->{'donneesFormulaire'} = $this->getDonneesFormulaireFactory()->get($id_d,$info_document['type']);
		$this->{'donneesFormulaire'}->getFormulaire()->setTabNumber($page);
		
		$this->{'actionPossible'} = $this->getActionPossible();
		$this->{'theAction'} = $documentType->getAction();
		$this->{'documentEntite'} = $this->getDocumentEntite();
		$this->{'my_role'} = $this->getDocumentEntite()->getRole($id_e,$id_d);
		$this->{'documentEmail'} = $this->getInstance('DocumentEmail');
		$this->{'documentActionEntite'} = $this->getDocumentActionEntite();
		
		$this->{'next_action_automatique'} =  $this->{'theAction'}->getActionAutomatique($true_last_action);
		$this->{'droit_erreur_fatale'} = $this->getRoleUtilisateur()->hasDroit($this->getId_u(),$info_document['type'].":edition",0);
		
		$this->{'is_super_admin'} = $this->getRoleUtilisateur()->hasDroit($this->getId_u(),"system:edition",0);
		if ($this->{'is_super_admin'}){
			$this->{'all_action'} = $documentType->getAction()->getWorkflowAction();
			
		}
		
		$this->{'page_title'} =  $info_document['titre'] . " (".$documentType->getName().")";
		
		if ($documentType->isAfficheOneTab()){
			$this->{'fieldDataList'} = $this->{'donneesFormulaire'}->getFieldDataListAllOnglet($this->{'my_role'});
		} else {
			$this->{'fieldDataList'} = $this->{'donneesFormulaire'}->getFieldDataList($this->{'my_role'},$page);
		}
		
		
		$this->{'recuperation_fichier_url'} = "Document/recuperationFichier?id_d=$id_d&id_e=$id_e";
		if ($this->hasDroit($this->{'id_e'},"system:lecture")) {
			$this->{'job_list'} = $this->getWorkerSQL()->getJobListWithWorkerForDocument($this->{'id_e'}, $this->{'id_d'});
		} else {
			$this->{'job_list'} = false;
		}
		$this->{'return_url'} = urlencode("Document/detail?id_e={$this->{'id_e'}}&id_d={$this->{'id_d'}}");
		
		$this->{'template_milieu'} = "DocumentDetail";
		$this->renderDefault();
	}
	
	public function editionAction(){
		$id_d = $this->getGetInfo()->get('id_d');
		$type = $this->getGetInfo()->get('type');
		$id_e = $this->getGetInfo()->getInt('id_e');
		$page = $this->getGetInfo()->getInt('page',0);
		$action = $this->getGetInfo()->get('action');
		
		$document = $this->getDocument();
		
		if ($action){
			$info = $document->getInfo($id_d);
			$type = $info['type'];
		} elseif ($id_d) {
			$info = $document->getInfo($id_d);
			$type = $info['type'];
			$action = 'modification';
		} else {
			$info = array();

			/** @var DocumentAPIController $documentAPIController */
			$documentAPIController = $this->getAPIController("Document");
			$documentAPIController->setRequestInfo(array('id_e'=>$id_e,'type'=>$type));
			$result = $documentAPIController->createAction();
			$id_d = $result['id_d'];
			$action = 'modification';
		}
		
		$this->verifDroit($id_e, $type.":edition","/Document/list");
		
		
		$actionPossible = $this->getActionPossible();
		
		if ( ! $actionPossible->isActionPossible($id_e,$this->getId_u(),$id_d,$action)) {
			$this->setLastError("L'action « $action »  n'est pas permise : " .$actionPossible->getLastBadRule() );
			header("Location: detail?id_d=$id_d&id_e=$id_e&page=$page");
			exit;
		}
		
		
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
		
		$infoEntite = $this->getEntiteSQL()->getInfo($id_e);
		
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d,$type);
		
		$formulaire = $donneesFormulaire->getFormulaire();
		if (! $formulaire->tabNumberExists($page)){
			$page = 0;
		}
		
		
		$this->{'inject'} = array('id_e'=>$id_e,'id_d'=>$id_d,'form_type'=>$type,'action'=>$action,'id_ce'=>'');
		
		
		$last_action = $this->getDocumentActionEntite()->getLastActionNotModif($id_e, $id_d);
		
		$editable_content = $documentType->getAction()->getEditableContent($last_action);
		
		if ( (! in_array($last_action,array("creation","modification"))) || $editable_content){
			if ($editable_content){
				$donneesFormulaire->setEditableContent($editable_content);
			}
		}
		
		$this->{'page_title'}="Edition d'un document « " . $documentType->getName() . " » ( " . $infoEntite['denomination'] . " ) ";
		
		$this->{'info'} = $info;
		$this->{'id_e'} = $id_e;
		$this->{'id_d'} = $id_d;
		$this->{'page'} = $page;
		$this->{'type'} = $type;
		$this->{'action'} = $action;
		$this->{'documentType'} = $documentType;
		$this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($id_e);
		$this->{'formulaire'} =  $documentType->getFormulaire();
		$this->{'donneesFormulaire'} = $donneesFormulaire;
		$this->{'ActionPossible'} = $this->getActionPossible();
		$this->{'theAction'} = $documentType->getAction();
		$this->{'documentEntite'} = $this->getDocumentEntite();
		$this->{'my_role'} = $this->getDocumentEntite()->getRole($id_e,$id_d);
		$this->{'documentEmail'} = $this->getInstance("DocumentEmail");
		$this->{'documentActionEntite'} = $this->getDocumentActionEntite();

		$this->{'action_url'} = "Document/doEdition";
		$this->{'recuperation_fichier_url'} = "Document/recuperationFichier?id_d=$id_d&id_e=$id_e";
		$this->{'suppression_fichier_url'} = "Document/supprimerFichier?id_d=$id_d&id_e=$id_e&page=$page&action=$action";
		$this->{'externalDataURL'} = "Document/externalData" ;
		
		$this->{'template_milieu'} = "DocumentEdition";
		$this->renderDefault();
	}
	
	
	public function indexAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->get('id_e',0);
		$offset = $recuperateur->getInt('offset',0);
		$search = $recuperateur->get('search');
		$limit = 20;
		
		$liste_type = array();
		$allDroit = $this->getRoleUtilisateur()->getAllDroit($this->getId_u());
		foreach($allDroit as $droit){
			if (preg_match('/^(.*):lecture$/',$droit,$result)){
				$liste_type[] = $result[1];
			}
		}	
		
		$liste_collectivite = $this->getRoleUtilisateur()->getEntiteWithSomeDroit($this->getId_u());
		
		if (! $id_e ) {
			if (count($liste_collectivite) == 0){
				$this->redirect("/Connexion/nodroit");
			}
			if (count($liste_collectivite) == 1){
				$id_e = $liste_collectivite[0];
			}
		}
		
		$this->{'tri'} =  $recuperateur->get('tri','date_dernier_etat');
		$this->{'sens_tri'} = $recuperateur->get('sens_tri','DESC');
		
		$this->{'url_tri'} = false;
		
		
		if ($id_e){						
			$this->{'listDocument'} = $this->getDocumentActionEntite()->getListDocumentByEntite($id_e,$liste_type,$offset,$limit,$search);
			$this->{'count'} = $this->getDocumentActionEntite()->getNbDocumentByEntite($id_e,$liste_type,$search);
			$this->{'type_list'} = $this->getAllType($this->{'listDocument'});
		}
		
		$this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($id_e);
		$this->{'id_e'} = $id_e;
		$this->{'search'} = $search;
		$this->{'offset'} = $offset;
		$this->{'limit'} = $limit;
		
		$this->{'champs_affiches'} = array('titre'=>'Objet','type'=>'Type','entite'=>'Entité','dernier_etat'=>'Dernier état','date_dernier_etat'=>'Date');
		
		$this->setNavigationInfo($id_e,"Document/index?a=a");
		$this->{'page_title'}= "Liste des documents " . $this->{'infoEntite'}['denomination'] ;
		$this->{'template_milieu'} = "DocumentIndex";
		$this->renderDefault();
	}
	
	private function getAllType(array $listDocument){
		$type = array();
		foreach($listDocument as $doc){
			$type[$doc['type']] = $doc['type'];
			
		}
		return array_keys($type);
	}

	public function listAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->get('id_e',0);
		$offset = $recuperateur->getInt('offset',0);
		$search = $recuperateur->get('search');
		$type = $recuperateur->get('type');
		$filtre = $recuperateur->get('filtre');
		$last_id = $recuperateur->get('last_id');
		
		$limit = 20;
		
		if (! $type){
			$this->redirect("/Document/index?id_e=$id_e");
		}
		
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
		
		$liste_collectivite = $this->getRoleUtilisateur()->getEntite($this->getId_u(),$type.":lecture");
		
		if ( ! $liste_collectivite){
			$this->redirect("/Document/index");
		}
		
		if (!$id_e && (count($liste_collectivite) == 1)){
			$id_e = $liste_collectivite[0];
			$this->{'id_e_menu'} = $id_e;
			$this->{'type_e_menu'} = $type;
		}
			
		
		$this->verifDroit($id_e, "$type:lecture");
		$this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($id_e);
		
		$page_title = "Liste des documents " . $documentType->getName();
		if ($id_e){
			$page_title .= " pour " . $this->{'infoEntite'}['denomination'];
		}
		
		$this->{'page_title'} = $page_title;
		$this->{'documentActionEntite'} = $this->getDocumentActionEntite();
		$this->{'ActionPossible'} = $this->getActionPossible();
		
		
		$this->{'all_action'} = $documentType->getAction()->getWorkflowAction();
		
		
		if ($this->getActionPossible()->isCreationPossible($id_e,$this->getId_u(),$type)){
			$this->{'nouveau_bouton_url'} = "Document/edition?type=$type&id_e=$id_e";
		}
		$this->{'id_e'} = $id_e;
		$this->{'search'} = $search;
		$this->{'offset'} = $offset;
		$this->{'limit'} = $limit;
		$this->{'filtre'} = $filtre;
		$this->{'last_id'} = $last_id;
		$this->{'type'} = $type;
		
		$this->{'tri'}=  $recuperateur->get('tri','date_dernier_etat');
		$this->{'sens_tri'}= $recuperateur->get('sens_tri','DESC');
		
		
		$this->{'documentTypeFactory'}= $this->getDocumentTypeFactory();
		$this->setNavigationInfo($id_e,"Document/list?type=$type");
		
		$this->{'champs_affiches'}= $documentType->getChampsAffiches();
		
		
		$this->{'allDroitEntite'}= $this->getRoleUtilisateur()->getAllDocumentLecture($this->getId_u(),$this->{'id_e'});
		
		$this->{'indexedFieldsList'}= $documentType->getFormulaire()->getIndexedFields();
		$indexedFieldValue = array();
		foreach($this->{'indexedFieldsList'} as $indexField => $indexLibelle){
			$indexedFieldValue[$indexField] = $recuperateur->get($indexField);
		}
		
		$this->{'listDocument'}= $this->getDocumentActionEntite()->getListBySearch($id_e,$type,
				$offset,$limit,$search,$filtre,false,false,$this->{'tri'},
				$this->{'allDroitEntite'},false,false,false,$indexedFieldValue,$this->{'sens_tri'}
		);
		
		
		$this->{'url_tri'}= "Document/list?id_e=$id_e&type=$type&search=$search&filtre=$filtre";
		
		$this->{'type_list'}= $this->getAllType($this->{'listDocument'});
		
		$this->{'template_milieu'}= "DocumentList";
		$this->renderDefault();
	}
	
	public function searchDocument(){
		$recuperateur = new Recuperateur($_REQUEST);
		$this->{'id_e'}= $recuperateur->get('id_e',0);
		$this->{'type'}= $recuperateur->get('type');
		$this->{'lastEtat'}= $recuperateur->get('lastetat');
		$this->{'last_state_begin'}= $recuperateur->get('last_state_begin');
		$this->{'last_state_end'}= $recuperateur->get('last_state_end');
		$this->{'state_begin'}= $recuperateur->get('state_begin');
		$this->{'state_end'}= $recuperateur->get('state_end');
		

		$this->{'last_state_begin_iso'}= getDateIso($this->{'last_state_begin'} );
		$this->{'last_state_end_iso'}= getDateIso($this->{'last_state_end'});
		$this->{'state_begin_iso'}=  getDateIso($this->{'state_begin'} );
		$this->{'state_end_iso'}=    getDateIso($this->{'state_end'} );

		if ( ! $this->{'id_e'} ){
			$error_message = "id_e est obligatoire";
			$this->setLastError($error_message);
			$this->redirect("");
		}
		$this->verifDroit($this->{'id_e'}, "entite:lecture");
		
		$this->{'allDroitEntite'}= $this->getRoleUtilisateur()->getAllDocumentLecture($this->getId_u(),$this->{'id_e'});
		
		$this->{'etatTransit'}= $recuperateur->get('etatTransit');
		

		$this->{'tri'}=  $recuperateur->get('tri','date_dernier_etat');
		$this->{'sens_tri'}= $recuperateur->get('sens_tri','DESC');
		$this->{'go'}= $recuperateur->get('go',0);
		$this->{'offset'}= $recuperateur->getInt('offset',0);
		$this->{'search'}= $recuperateur->get('search');
		$this->{'limit'}= $recuperateur->getInt('limit',100);

		$indexedFieldValue = array();
		if ($this->{'type'}) {
			$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($this->{'type'});
			$this->{'indexedFieldsList'}= $documentType->getFormulaire()->getIndexedFields();
			
			foreach($this->{'indexedFieldsList'} as $indexField => $indexLibelle){
				$indexedFieldValue[$indexField] = $recuperateur->get($indexField);
				if ($documentType->getFormulaire()->getField($indexField)->getType() == 'date'){
					$indexedFieldValue[$indexField] = date_fr_to_iso($recuperateur->get($indexField));
				}
			}
			$this->{'champs_affiches'}= $documentType->getChampsAffiches();
		} else {
			$this->{'champs_affiches'}= array('titre'=>'Objet','type'=>'Type','entite'=>'Entité','dernier_etat'=>'Dernier état','date_dernier_etat'=>'Date');
			$this->{'indexedFieldsList'}= array();
		}
				
		$this->{'indexedFieldValue'}= $indexedFieldValue;
		
		
		$allDroit = $this->getRoleUtilisateur()->getAllDroit($this->getId_u());		
		$this->{'listeEtat'}= $this->getDocumentTypeFactory()->getActionByRole($allDroit);
		
		$this->{'documentActionEntite'}= $this->getDocumentActionEntite();
		$this->{'documentTypeFactory'}= $this->getDocumentTypeFactory();
		
		$this->{'my_id_e'} = $this->{'id_e'};

		/** @var DocumentAPIController $documentAPIController */
		$documentAPIController = $this->getAPIController('Document');

		try {
			$this->{'listDocument'}= $documentAPIController->rechercheAction();
		} catch(Exception $e){
			$this->setLastError($e->getMessage());
			$this->redirect("");
		}

		$url_tri = "Document/search?id_e={$this->{'id_e'}}&search={$this->{'search'}}&type={$this->{'type'}}&lastetat={$this->{'lastEtat'}}".
						"&last_state_begin={$this->{'last_state_begin_iso'}}&last_state_end={$this->{'last_state_end_iso'}}&etatTransit={$this->{'etatTransit'}}".
						"&state_begin={$this->{'state_begin_iso'}}&state_end={$this->{'state_end_iso'}}&date_in_fr=true";

		if ($this->{'type'}){
			foreach($indexedFieldValue as $indexName => $indexValue){
				$url_tri.="&".urlencode($indexName)."=".urlencode($indexValue);
			}
		}
		
		$this->{'url_tri'}= $url_tri;
		$this->{'type_list'}= $this->getAllType($this->{'listDocument'});
	}
	
	public function exportAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->get('id_e',0);
		$type = $recuperateur->get('type');
		$search = $recuperateur->get('search');
		
		$lastEtat = $recuperateur->get('lastetat');
		$last_state_begin = $recuperateur->get('last_state_begin');
		$last_state_end = $recuperateur->get('last_state_end');
		
		$last_state_begin_iso = getDateIso($last_state_begin);
		$last_state_end_iso = getDateIso($last_state_end);
		
		$etatTransit = $recuperateur->get('etatTransit');
		$state_begin =  $recuperateur->get('state_begin');
		$state_end =  $recuperateur->get('state_end');
		$tri =  $recuperateur->get('tri');
		$sens_tri = $recuperateur->get('sens_tri');
		
		$offset = 0;

		$allDroitEntite = $this->getRoleUtilisateur()->getAllDocumentLecture($this->getId_u(),$id_e);
		
		
		$indexedFieldValue = array();
		if ($type) {
			$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
			$indexedFieldsList = $documentType->getFormulaire()->getIndexedFields();
			foreach($indexedFieldsList as $indexField => $indexLibelle){
				$indexedFieldValue[$indexField]=$recuperateur->get($indexField);
			}
			/*$champs_affiches = $documentType->getChampsAffiches();*/
		} else {
			//$champs_affiches = array('titre'=>'Objet','type'=>'Type','entite'=>'Entité','dernier_etat'=>'Dernier état','date_dernier_etat'=>'Date');
			$indexedFieldsList = array();
				
		}
		
		
		$limit = $this->getDocumentActionEntite()->getNbDocumentBySearch($id_e,$type,$search,$lastEtat,$last_state_begin_iso,$last_state_end_iso,$allDroitEntite,$etatTransit,$state_begin,$state_end,$indexedFieldValue);
		$listDocument = $this->getDocumentActionEntite()->getListBySearch($id_e,$type,$offset,$limit,$search,$lastEtat,$last_state_begin_iso,$last_state_end_iso,$tri,$allDroitEntite,$etatTransit,$state_begin,$state_end,$indexedFieldValue,$sens_tri);
		
		$line = array("ENTITE","ID_D","TYPE","TITRE","DERNIERE ACTION","DATE DERNIERE ACTION");
		foreach($indexedFieldsList as $indexField=>$indexLibelle){
			$line[] = $indexLibelle;
		}
		$result = array($line);
		foreach($listDocument as $i => $document){
			 $line = array(
					$document['denomination'],
					$document['id_d'],
			 		$document['type'],
					$document['titre'],
					$document['last_action'],
					$document['last_action_date'],
						
			);
			foreach($indexedFieldsList as $indexField=>$indexLibelle){
				$line[] = $this->getInstance("DocumentIndexSQL")->get($document['id_d'],$indexField);
			}
			$result[] = $line;
		}
	
		$this->getInstance("CSVoutput")->sendAttachment("pastell-export-$id_e-$type-$search-$lastEtat-$tri.csv",$result);
	}
	
	
	public function searchAction(){				
		$this->searchDocument();
		$this->{'page_title'}= "Recherche avancée de document";
		$this->{'template_milieu'}= "DocumentSearch";
		$this->renderDefault();
	}
	
	public function warningAction(){
		$recuperateur = new Recuperateur($_GET);
		$this->{'id_d'}= $recuperateur->get('id_d');
		$this->{'action'}= $recuperateur->get('action');
		$this->{'id_e'}= $recuperateur->get('id_e');
		$this->{'page'}= $recuperateur->getInt('page',0);
		
		
		$this->{'infoDocument'}= $this->getDocument()->getInfo($this->{'id_d'});
		
		$type = $this->{'infoDocument'}['type'];
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
		$theAction = $documentType->getAction();
		
		$this->{'actionName'}= $theAction->getDoActionName($this->{'action'});
		
		$this->{'page_title'} = "Attention ! Action irréversible";
		$this->{'template_milieu'}= "DocumentWarning";
		$this->renderDefault();
	}
	
	
	private function validTraitementParLot($input){
		$recuperateur = new Recuperateur($input);
		$this->{'id_e'}= $recuperateur->get('id_e',0);
		$this->{'offset'}= $recuperateur->getInt('offset',0);
		$this->{'search'}= $recuperateur->get('search');
		$this->{'type'}= $recuperateur->get('type');
		$this->{'filtre'}= $recuperateur->get('filtre');
		$this->{'limit'}= 20;
		
		if (! $this->{'type'}){
			$this->redirect("/Document/index?id_e={$this->{'id_e'}}");
		}
		if (!$this->{'id_e'}){
			$this->redirect("/Document/index");
		}
		
		$this->{'id_e_menu'}= $this->{'id_e'};
		$this->verifDroit($this->{'id_e'}, "{$this->{'type'}}:lecture");
		$this->{'infoEntite'}= $this->getEntiteSQL()->getInfo($this->{'id_e'});
		
		$this->{'id_e_menu'}= $this->{'id_e'};
		$this->{'type_e_menu'}= $this->{'type'};
	}
	
	public function traitementLotAction(){
		$this->validTraitementParLot($_GET);
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($this->{'type'});
		$page_title = "Traitement par lot pour les  documents " . $documentType->getName();
		$page_title .= " pour " . $this->{'infoEntite'}['denomination'];
		$this->{'page_title'}= $page_title;
		
		$this->{'documentTypeFactory'}= $this->getDocumentTypeFactory();
		$this->setNavigationInfo($this->{'id_e'},"Document/list?type={$this->{'type'}}");
		$this->{'theAction'}= $documentType->getAction();
		
		$listDocument = $this->getDocumentActionEntite()->getListDocument(
			$this->{'id_e'} ,
			$this->{'type'} ,
			$this->{'offset'},
			$this->{'limit'},
			$this->{'search'},
			$this->{'filtre'}
		) ;
		
		$all_action = array();
		foreach($listDocument as $i => $document){
			$listDocument[$i]['action_possible'] =  $this->getActionPossible()->getActionPossibleLot($this->{'id_e'},$this->getId_u(),$document['id_d']);
			$all_action = array_merge($all_action,$listDocument[$i]['action_possible']);
		}
		$this->{'listDocument'}= $listDocument;
		
		$all_action = array_unique($all_action);
		
		$this->{'all_action'}= $all_action;
		$this->{'type_list'}= $this->getAllType($this->{'listDocument'});
		$this->{'template_milieu'}= "DocumentTraitementLot";
		$this->renderDefault();
	}
	
	public function confirmTraitementLotAction(){
		$this->validTraitementParLot($_GET);
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($this->{'type'});
		$this->{'page_title'}= "Confirmation du traitement par lot pour les  documents " . $documentType->getName() ." pour " .
			$this->{'infoEntite'}['denomination'];
		
		$this->{'url_retour'}= "document/traitementLot?id_e={$this->{'id_e'}}&type={$this->{'type'}}&search={$this->{'search'}}&filtre={$this->{'filtre'}}&offset={$this->{'offset'}}";
		
		$recuperateur = new Recuperateur($_GET);
		$this->{'action_selected'}= $recuperateur->get('action');
		$this->{'theAction'}= $documentType->getAction();
		
		$action_libelle = $this->{'theAction'}->getActionName($this->{'action_selected'});
		
		$all_id_d = $recuperateur->get('id_d');
		if (! $all_id_d){
			$this->setLastError("Vous devez sélectionner au moins un document");
			$this->redirect($this->{'url_retour'});
		}
		
		$error = "";
		$listDocument = array();
		foreach($all_id_d as $id_d){
			$infoDocument  = $this->getDocumentActionEntite()->getInfo($id_d,$this->{'id_e'});
			if (! $this->getActionPossible()->isActionPossible($this->{'id_e'},$this->getId_u(),$id_d,$this->{'action_selected'})){
				$error .= "L'action « $action_libelle » n'est pas possible pour le document « {$infoDocument['titre']} »<br/>";
			}
			if ($this->getInstance("JobManager")->hasActionProgramme($this->{'id_e'},$id_d)){
				$error .= "Il y a déjà une action programmée pour le document « {$infoDocument['titre']} »<br/>";
			}
			$listDocument[] = $infoDocument;
		}
		if ($error){
			$this->setLastError($error."<br/><br/>Aucune action n'a été executée");
			$this->redirect($this->{'url_retour'});
		}
				
		$this->{'listDocument'}= $listDocument;
		$this->{'template_milieu'}= "DocumentConfirmTraitementLot";
		$this->renderDefault();
	}
	
	public function doTraitementLotAction(){
		$this->validTraitementParLot($_POST);
		$recuperateur = new Recuperateur($_POST);
		$action_selected = $recuperateur->get('action');
		$all_id_d = $recuperateur->get('id_d');
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($this->{'type'});
		
		$action_libelle = $documentType->getAction()->getDoActionName($action_selected);

		$error = "";
		$message ="";
		foreach($all_id_d as $id_d){
			$infoDocument  = $this->getDocumentActionEntite()->getInfo($id_d,$this->{'id_e'});
			if (! $this->getActionPossible()->isActionPossible($this->{'id_e'},$this->getId_u(),$id_d,$action_selected)){
				$error .= "L'action « $action_libelle » n'est pas possible pour le document « {$infoDocument['titre']} »<br/>";
			} 
			
			if ($this->getInstance("JobManager")->hasActionProgramme($this->{'id_e'},$id_d)){
				$error .= "Il y a déjà une action programmée pour le document « {$infoDocument['titre']} »<br/>";
			}
			
			$listDocument[] = $infoDocument;
			$message .= "L'action « $action_libelle » est programmée pour le document « {$infoDocument['titre']} »<br/>";	
		
		}
		if ($error){
			$this->setLastError($error."<br/><br/>Aucune action n'a été executée");
			$this->redirect($this->{'url_retour'});
		}
		
		$this->getActionExecutorFactory()->executeLotDocument($this->{'id_e'},$this->getId_u(),$all_id_d,$action_selected);
		$this->setLastMessage($message);
		$url_retour = "Document/list?id_e={$this->{'id_e'}}&type={$this->{'type'}}&search={$this->{'search'}}&filtre={$this->{'filtre'}}&offset={$this->{'offset'}}";
		$this->redirect($url_retour);
	}
	
	public function retourTeletransmissionAction(){
	
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->get('id_e',0);
		$id_u = $recuperateur->get('id_u');
		$type = $recuperateur->get('type');
		$all_id_d = $recuperateur->get('id_d');
	
		$url_retour = "Document/list?id_e={$id_e}&type={$type}";
		$message ="";

		/* FIXME FIXME : Il y a une référence vers un connecteur !!! */
		/** @var TdtConnecteur $tdt */
		$tdt = $this->getConnecteurFactory()->getConnecteurByType($id_e,$type,'TdT');
	
		foreach($all_id_d as $id_d){
			$infoDocument  = $this->getDocumentActionEntite()->getInfo($id_d,$id_e);
			$listDocument[] = $infoDocument;
				
			$tedetis_transaction_id = $this->getDonneesFormulaireFactory()->get($id_d)->get('tedetis_transaction_id');
			$status =  $tdt->getStatus($tedetis_transaction_id);
				
			if (in_array($status, array(TdtConnecteur::STATUS_ACTES_EN_ATTENTE_DE_POSTER))){
				$message .= "La transaction pour le document « {$infoDocument['titre']} » n'a pas le bon status : ".TdtConnecteur::getStatusString($status)." trouvé<br/>";
			}
			else {
				$this->getActionChange()->addAction($id_d,$id_e,$id_u,"send-tdt","Le document a été télétransmis à la préfecture");
				$message .= "Le document « {$infoDocument['titre']} » a été télétransmis<br/>";
			}
			$this->getInstance("JobManager")->setJobForDocument($id_e, $id_d,"suite traitement par lot");
		}
	
		$this->setLastMessage($message);
		$this->redirect($url_retour);	
	}

	
	public function reindex($document_type,$field_name,$offset=0,$limit=-1){
		if (! $this->getDocumentTypeFactory()->isTypePresent($document_type)){
			echo "[ERREUR] Le type de document $document_type n'existe pas sur cette plateforme.\n";
			return;
		}
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($document_type);
		$formulaire = $documentType->getFormulaire();
		
		
		$field = $formulaire->getField($field_name);
		if (! $field){
			echo "[ERREUR] Le champs $field_name n'existe pas pour le type de document $document_type\n";
			return;
		}
		if (! $field->isIndexed()){
			echo "[ERREUR] Le champs $document_type:$field_name n'est pas indexé\n";
			return;
		}

		$document_list = $this->getDocument()->getAllByType($document_type);
		if ($limit > 0){
			$document_list = array_slice($document_list,$offset,$limit);
		}
		
		foreach($document_list as $document_info){
			echo "Réindexation du document {$document_info['titre']} ({$document_info['id_d']})\n";
			$documentIndexor = new DocumentIndexor($this->getInstance("DocumentIndexSQL"), $document_info['id_d']);
			$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document_info['id_d']);
			$fieldData = $donneesFormulaire->getFieldData($field_name);
			
			$documentIndexor->index($field_name, $fieldData->getValueForIndex());
		}
	}
	
	public function fixModuleChamps($document_type,$old_field_name,$new_field_name){
		foreach($this->getDocument()->getAllByType($document_type) as $document_info){
			$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document_info['id_d']);
			$value = $donneesFormulaire->get($old_field_name);
			$donneesFormulaire->setData($new_field_name,$value);
			$donneesFormulaire->deleteField($old_field_name);
			
			echo $document_info['id_d'] ." : OK\n"; 
		}
	}
	
	public function visionneuseAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_d = $recuperateur->get('id_d');
		$id_e = $recuperateur->getInt('id_e');
		$field = $recuperateur->get('field');
		$num = $recuperateur->getInt('num',0);
		
		$this->verifDroitLecture($id_e, $id_d);
		
		$this->getInstance("VisionneuseFactory")->display($id_d,$field,$num);
	}
	
	public function changeEtatAction(){
		if (! $this->getRoleUtilisateur()->hasDroit($this->getId_u(),"system:edition",0)){
			$this->redirect("");
		}
		
		$recuperateur = new Recuperateur($_POST);
		$id_d = $recuperateur->get('id_d');
		$id_e = $recuperateur->getInt('id_e');
		$action = $recuperateur->get('action');
		$message = $recuperateur->get('message');
		
		if ($action){
			$this->getActionChange()->addAction($id_d,$id_e,$this->getId_u(),$action,"Modification manuelle de l'état - $message");
			$this->setLastMessage("L'état du document a été modifié : -> $action");
		}
		
		$this->redirect("Document/detail?id_d=$id_d&id_e=$id_e");
	}
	
	
	
	public function bulkModification($id_e,$type,$etat,$field_name,$field_value){
		$result = $this->getDocumentActionEntite()->getDocument($id_e,$type,$etat);
		
		if (!$result){
			throw new Exception("Il n'y a pas de document de type $type pour l'id_e $id_e");
		}
		foreach($result as $document_info){
			$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document_info['id_d'],$type);
			$donneesFormulaire->setData($field_name, $field_value);
		}
		return count($result);
	}

	public function actionAction(){
		$recuperateur = new Recuperateur($_REQUEST);
		$id_d = $recuperateur->get('id_d');
		$action = $recuperateur->get('action');
		$id_e = $recuperateur->get('id_e');
		$page = $recuperateur->getInt('page',0);
		$go = $recuperateur->getInt('go',0);

		/** @var Document $document */
		$document = $this->getDocument();
		$infoDocument = $document->getInfo($id_d);
		$type = $infoDocument['type'];

		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
		$theAction = $documentType->getAction();

		$actionPossible = $this->getActionPossible();

		if ( ! $actionPossible->isActionPossible($id_e,$this->getId_u(),$id_d,$action)) {
			$this->setLastError("L'action « $action »  n'est pas permise : " .$actionPossible->getLastBadRule() );
			$this->redirect("/Document/detail?id_d=$id_d&id_e=$id_e&page=$page");
		}

		if ($action == Action::MODIFICATION){
			$this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&page=$page");
		}


		$id_destinataire = $recuperateur->get('destinataire')?:array();

		$action_destinataire =  $theAction->getActionDestinataire($action);
		if ($action_destinataire) {
			if (! $id_destinataire){
				$this->redirect("/Entite/choix?id_d=$id_d&id_e=$id_e&action=$action&type=$action_destinataire");
			}
		}

		if ($theAction->getWarning($action) && ! $go){
			$this->redirect("/Document/warning?id_d=$id_d&id_e=$id_e&action=$action&page=$page");

		}
		$result = $this->getActionExecutorFactory()->executeOnDocument($id_e,$this->getId_u(),$id_d,$action,$id_destinataire);
		$message = $this->getActionExecutorFactory()->getLastMessage();

		if (! $result ){
			$this->setLastError($message);
		} else {
			$this->setLastMessage($message);
		}
		$this->redirect("/Document/detail?id_d=$id_d&id_e=$id_e&page=$page");
	}

	public function doEditionAction(){

		$recuperateur = new Recuperateur($_POST);
		$id_d = $recuperateur->get('id_d');
		$type = $recuperateur->get('form_type');
		$id_e = $recuperateur->get('id_e');
		$page = $recuperateur->get('page');
		$action = $recuperateur->get('action');


		if ( ! $this->getRoleUtilisateur()->hasDroit($this->getId_u(),$type.":edition",$id_e)) {
			$this->redirect("/Document/list");
		}

		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
		$formulaire = $documentType->getFormulaire();

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d,$type);

		$document = $this->getDocument();
		$info = $document->getInfo($id_d);
		if (! $info){
			$document->save($id_d,$type);
			$action = 'creation';
		}

		if (!$action){
			$action = 'modification';
		}


		/** @var ActionPossible $actionPossible */
		$actionPossible = $this->getActionPossible();

		if ( ! $actionPossible->isActionPossible($id_e,$this->getId_u(),$id_d,$action)) {
			$this->setLastError("L'action « $action »  n'est pas permise : " .$actionPossible->getLastBadRule() );
			$this->redirect("/Document/detail?id_d=$id_d&id_e=$id_e&page=$page");
		}

		$last_action = $this->getDocumentActionEntite()->getLastActionNotModif($id_e, $id_d);

		$editable_content = $documentType->getAction()->getEditableContent($last_action);


		if ( (! in_array($last_action,array("creation","modification"))) || $editable_content){
			if ($editable_content){
				$donneesFormulaire->setEditableContent($editable_content);
			}
		}

		$fileUploader = new FileUploader();

		$donneesFormulaire->saveTab($recuperateur,$fileUploader,$page);

		if ($action=='creation' || $action=='modification'){
			$documentEntite = new DocumentEntite($this->getSQLQuery());
			if ( ! $documentEntite->getRole($id_e, $id_d)) {
				$documentEntite->addRole($id_d,$id_e,"editeur");
			}
		}



		if (! $info){
			$this->getActionChange()->addAction($id_d,$id_e,$this->getId_u(),Action::CREATION,"Création du document");
		} else if ($donneesFormulaire->isModified() ) {

			/** @var BaseAPIControllerFactory $baseAPIControllerFactory */
			$baseAPIControllerFactory = $this->getInstance('BaseAPIControllerFactory');
			/** @var DocumentAPIController $documentController */
			$documentController = $baseAPIControllerFactory->getInstance('Document',$this->getId_u());

			if ($documentController->needChangeEtatToModification($id_e,$id_d,$documentType)) {
				$this->getActionChange()->updateModification($id_d, $id_e, $this->getId_u(), $action);
			}
		}


		$titre_field = $formulaire->getTitreField();
		$titre = $donneesFormulaire->get($titre_field);

		$document->setTitre($id_d,$titre);


		foreach($donneesFormulaire->getOnChangeAction() as $action_on_change) {
			$result = $this->getActionExecutorFactory()->executeOnDocument($id_e,$this->getId_u(),$id_d,$action_on_change);
			if (!$result){
				$this->setLastError($this->getActionExecutorFactory()->getLastMessage());
			} elseif ($this->getActionExecutorFactory()->getLastMessage()){
				$this->setLastMessage($this->getActionExecutorFactory()->getLastMessage());
			}
		}



		if ( $recuperateur->get('ajouter') ){
			$this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&page=$page&action=$action");
		}
		if ( $recuperateur->get('suivant') ){
			$this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&action=$action&page=".($page+1));
		}

		if ($recuperateur->get('precedent')){
			$this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&action=$action&page=".($page - 1));
		}

		$this->redirect("/Document/detail?id_d=$id_d&id_e=$id_e&page=$page&action=$action");
	}

	public function externalDataAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_d = $recuperateur->get('id_d');
		$id_e = $recuperateur->get('id_e');
		$field = $recuperateur->get('field');
		$page = $recuperateur->get('page');


		$document = $this->getDocument();

		$info = $document->getInfo($id_d);
		$type = $info['type'];

		if (  ! $this->getRoleUtilisateur()->hasDroit($this->getId_u(),$type.":edition",$id_e)) {
			$this->setLastError("Vous n'avez pas le droit de faire cette action ($type:edition)");
			$this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e");
		}

		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
		$formulaire = $documentType->getFormulaire();

		$theField = $formulaire->getField($field);

		try {
			$action_name = $theField->getProperties('choice-action');
			$this->getActionExecutorFactory()->displayChoice($id_e,$this->getId_u(),$id_d,$action_name,false,$field,$page);
		} catch (Exception $e){
			$this->setLastError($e->getMessage());
			$this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&page=$page");
		}
	}

	public function doExternalDataAction(){
		$recuperateur = new Recuperateur($_REQUEST);
		$id_d = $recuperateur->get('id_d');
		$id_e = $recuperateur->get('id_e');
		$field = $recuperateur->get('field');
		$page = $recuperateur->get('page');

		$document = $this->getDocument();
		$info = $document->getInfo($id_d);
		$type = $info['type'];

		if ( ! $this->getRoleUtilisateur()->hasDroit($this->getId_u(),$type.":edition",$id_e)) {
			$this->setLastError("Vous n'avez pas le droit de faire cette action ($type:edition)");
			$this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e");
		}

		$actionPossible = $this->getActionPossible();


		if ( ! $actionPossible->isActionPossible($id_e,$this->getId_u(),$id_d,'modification') ) {
			$this->setLastError("L'action « modification »  n'est pas permise : " .$actionPossible->getLastBadRule() );
			header("Location: detail?id_d=$id_d&id_e=$id_e&page=$page");
			exit;
		}

		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
		$formulaire = $documentType->getFormulaire();
		$formulaire->setTabNumber($page);

		$theField = $formulaire->getField($field);


		$action_name = $theField->getProperties('choice-action');
		
		$this->getActionExecutorFactory()->goChoice($id_e,$this->getId_u(),$id_d,$action_name,false,$field,$page);
	}

	public function recuperationFichierAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_d = $recuperateur->get('id_d');
		$id_e = $recuperateur->get('id_e');
		$field = $recuperateur->get('field');
		$num = $recuperateur->getInt('num');


		$document = $this->getDocument();
		$info = $document->getInfo($id_d);


		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d,$info['type']);


		$file_path = $donneesFormulaire->getFilePath($field,$num);
		$file_name_array = $donneesFormulaire->get($field);
		$file_name= $file_name_array[$num];

		if (! file_exists($file_path)){
			$this->setLastError("Ce fichier n'existe pas");
			$this->redirect();
		}

		$utilisateur = new Utilisateur($this->getSQLQuery());
		$infoUtilisateur = $utilisateur->getInfo($this->getId_u());
		$nom = $infoUtilisateur['prenom']." ".$infoUtilisateur['nom'];

		$this->getJournal()->add(Journal::DOCUMENT_CONSULTATION,$id_e,$id_d,"Consulté","$nom a consulté le document $file_name");

		if (mb_strlen($file_name) > 80){
			$pos = mb_strrpos($file_name,".");
			$name = mb_substr($file_name,0,$pos);
			$extension = mb_substr($file_name,$pos + 1 ,mb_strlen($file_name));
			$file_name = mb_substr($name,0,76).".".$extension;
		}

		header("Content-type: ".mime_content_type($file_path));
		header("Content-disposition: attachment; filename=\"".urlencode($file_name)."\"");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");

		readfile($file_path);
	}

	public function supprimerFichierAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_d = $recuperateur->get('id_d');
		$page = $recuperateur->get('page');
		$id_e = $recuperateur->get('id_e');
		$field = $recuperateur->get('field');
		$num = $recuperateur->getInt('num',0);
		$action = $recuperateur->get('action');


		$document = $this->getDocument();
		$info = $document->getInfo($id_d);

		$type = $info['type'];

		if ( ! $this->getRoleUtilisateur()->hasDroit($this->getId_u(),$type.":edition",$id_e)) {
			$this->redirect("/Document/list");
		}

		$actionPossible = $this->getActionPossible();

		if (!$action){
			$action = 'modification';
		}

		if ( ! $actionPossible->isActionPossible($id_e,$this->getId_u(),$id_d,$action) ) {
			$this->setLastError("L'action « $action »  n'est pas permise : " .$actionPossible->getLastBadRule() );
			$this->redirect("/Document/detail?id_d=$id_d&id_e=$id_e&page=$page");
		}

		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
		$formulaire = $documentType->getFormulaire();
		$formulaire->setTabNumber($page);


		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d,$type);
		$donneesFormulaire->removeFile($field,$num);

		$this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&page=$page");
	}


}