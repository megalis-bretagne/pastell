<?php 

class APIAction {
	
	const RESULT_OK = "ok";
	
	private $objectInstancier;
	private $id_u;
	
	public function __construct(ObjectInstancier $objectInstancier,$id_u){
		$this->objectInstancier = $objectInstancier;
		$this->id_u = $id_u;
	}
	
	//SOAP passe la valeur NULL si on ne précise pas de valeur 
	private function setDefault(& $variable,$default){
		if (! $variable){
			$variable = $default;
		}
	}
	
	private function verifDroit($id_e,$droit){
		if  (! $this->objectInstancier->RoleUtilisateur->hasDroit($this->id_u,$droit,$id_e)){
			throw new Exception("Acces interdit id_e=$id_e, droit=$droit,id_u={$this->id_u}");
		}
	}
	
	private function getError($Errormessage){
		$result['status'] = 'error';
		$result['error-message'] = $Errormessage;;
		return $result;
	}
	
	public function version(){
		$info = $this->objectInstancier->ManifestFactory->getPastellManifest()->getInfo();
		$result = array();
		$result['version'] = $info['version'];
		$result['revision'] = $info['revision'];
		$result['version_complete'] = $info['version-complete'];
		$result['version-complete'] = $info['version-complete'];
		return $result; 
	}
	
	public function documentType(){
		$allDocType = $this->objectInstancier->DocumentTypeFactory->getAllType();
		$allDroit = $this->objectInstancier->RoleUtilisateur->getAllDroit($this->id_u);
		
		foreach($allDocType as $type_flux => $les_flux){
			foreach($les_flux as $nom => $affichage) {
				if ($this->objectInstancier->RoleUtilisateur->hasOneDroit($this->id_u,$nom.":lecture")){
					$allType[$nom]  = array('type'=>$type_flux,'nom'=>$affichage);
				}
			}
		}		
		return $allType;
	}
	
	public function documentTypeInfo($type){
		$this->setDefault($type,'');
		if ( !  $this->objectInstancier->RoleUtilisateur->hasOneDroit($this->id_u,"$type:lecture")) {
				throw new Exception("Acces interdit type=$type,id_u=$this->id_u");
		}
		
		$documentType = $this->objectInstancier->documentTypeFactory->getFluxDocumentType($type);
		$formulaire = $documentType->getFormulaire();
		
		foreach($formulaire->getAllFields() as $key => $fields){	
			$result[$key] = $fields->getAllProperties(); 	
		}
		return $result;
	}
	
	public function documentTypeAction($type){
		$this->setDefault($type,'');
		if ( !  $this->objectInstancier->RoleUtilisateur->hasOneDroit($this->id_u,"$type:lecture")) {
				throw new Exception("Acces interdit type=$type,id_u=$this->id_u");
		}
		
		$documentType = $this->objectInstancier->documentTypeFactory->getFluxDocumentType($type);
		return $documentType->getTabAction();
	}
	
	
	public function listEntite(){
		return $this->objectInstancier->RoleUtilisateur->getAllEntiteWithFille($this->id_u,'entite:lecture');
	}
	
	public function listDocument($id_e,$type,$offset,$limit){
		$this->setDefault($id_e,0);
		$this->setDefault($type,'');
		$this->setDefault($offset,0);
		$this->setDefault($limit,100);
		$this->verifDroit($id_e,"$type:lecture");
		return $this->objectInstancier->DocumentActionEntite->getListDocument($id_e , $type , $offset, $limit) ;
	}
	
	public function rechercheDocument(){
		$this->objectInstancier->DocumentControler->searchDocument(true,true);
		$list = $this->objectInstancier->DocumentControler->listDocument;
		return $list;
	}

	public function detailDocument($id_e,$id_d){
		$document = $this->objectInstancier->Document;
		$info = $document->getInfo($id_d);
		$result['info'] = $info;
		
		$this->verifDroit($id_e,$info['type'].":edition");
		
		$donneesFormulaire  = $this->objectInstancier->DonneesFormulaireFactory->get($id_d,$info['type']);
		$actionPossible = $this->objectInstancier->ActionPossible;
		
		$result['data'] = $donneesFormulaire->getRawData();
		$result['action-possible'] = $actionPossible->getActionPossible($id_e,$this->id_u,$id_d);
		$result['action_possible'] = $result['action-possible']; 
		
		$result['last_action'] = $this->objectInstancier->DocumentActionEntite->getLastActionInfo($id_e,$id_d);
		
		return $result;
	}
	
	public function detailSeveralDocument($id_e,array $all_id_d){
		$max_execution_time= ini_get('max_execution_time');
		$result = array();
		foreach($all_id_d as $id_d) {
			ini_set('max_execution_time', $max_execution_time);
			$result[$id_d] = $this->detailDocument($id_e, $id_d);
			$this->objectInstancier->DonneesFormulaireFactory->clearCache();
		} 
		return $result;
	}
	
	public function createDocument($id_e,$type){
		$this->verifDroit($id_e,"$type:edition");
		$document = $this->objectInstancier->Document;
		$id_d = $document->getNewId();	
		$document->save($id_d,$type);
		$this->objectInstancier->DocumentEntite->addRole($id_d,$id_e,"editeur");
		
		$actionCreator = new ActionCreator($this->objectInstancier->SQLQuery, $this->objectInstancier->Journal, $id_d);
		$actionCreator->addAction($id_e,$this->id_u,Action::CREATION,"Création du document [webservice]");
		
		$info['id_d'] = $id_d;
		return $info;
	}

	public function externalData($id_e, $id_d,$field){
		$document = $this->objectInstancier->Document;
		$info = $document->getInfo($id_d);
		
		$this->verifDroit($id_e,"{$info['type']}:edition");
				
		$documentType =  $this->objectInstancier->documentTypeFactory->getFluxDocumentType($info['type']);
		$formulaire = $documentType->getFormulaire();
		$theField = $formulaire->getField($field);
		
		if ( ! $theField ){
			throw new Exception("Type $field introuvable");
		}
		
		$action_name = $theField->getProperties('choice-action');
		return $this->objectInstancier->ActionExecutorFactory->displayChoice($id_e,$this->id_u,$id_d,$action_name,true,$field);	
	}
	
	public function modifDocument($data,FileUploader $fileUploader = null){
		$id_e = $data['id_e'];
		$id_d = $data['id_d'];
		$document = $this->objectInstancier->Document;
		$info = $document->getInfo($id_d);
		$this->verifDroit($id_e, "{$info['type']}:edition");
		
		unset($data['id_e']);
		unset($data['id_d']);
		
		$donneesFormulaire = $this->objectInstancier->DonneesFormulaireFactory->get($id_d);
		$actionPossible = $this->objectInstancier->ActionPossible;
		
		if ( ! $actionPossible->isActionPossible($id_e,$this->id_u,$id_d,'modification')) {
			throw new Exception("L'action « modification »  n'est pas permise");
		}
		
		$donneesFormulaire->setTabDataVerif($data);
		if ($fileUploader) {
			$donneesFormulaire->saveAllFile($fileUploader);
		} 
		return $this->changeDocumentFormulaire($id_e,$id_d,$info['type'],$donneesFormulaire);
	}

	public function sendFile($id_e, $id_d,$field_name, $file_name,$file_number,$file_content){
		$document = $this->objectInstancier->Document;
		$info = $document->getInfo($id_d);
		$this->verifDroit($id_e, "{$info['type']}:edition");
		$donneesFormulaire = $this->objectInstancier->DonneesFormulaireFactory->get($id_d,$info['type']);
		$donneesFormulaire->addFileFromData($field_name,$file_name,$file_content,$file_number);
		return $this->changeDocumentFormulaire($id_e,$id_d,$info['type'],$donneesFormulaire);
	}
	
	private function changeDocumentFormulaire($id_e,$id_d, $type,DonneesFormulaire $donneesFormulaire){
		$documentType = $this->objectInstancier->DocumentTypeFactory->getFluxDocumentType($type);
		$formulaire = $documentType->getFormulaire();
	
		$titre_field = $formulaire->getTitreField();
		$titre = $donneesFormulaire->get($titre_field);
		
		$document = $this->objectInstancier->Document;
		$document->setTitre($id_d,$titre);
		
		foreach($donneesFormulaire->getOnChangeAction() as $action) {	
			$this->objectInstancier->ActionExecutorFactory->executeOnDocument($id_e,$this->id_u,$id_d,$action,array(),true);
		}
				
		$actionCreator = new ActionCreator($this->objectInstancier->SQLQuery,$this->objectInstancier->Journal,$id_d);
		$actionCreator->addAction($id_e,$this->id_u,Action::MODIFICATION,"Modification du document [WS]");
		
		$result['result'] = self::RESULT_OK;
		$result['formulaire_ok'] = $donneesFormulaire->isValidable()?1:0;
		if (! $result['formulaire_ok']){
			$result['message'] = $donneesFormulaire->getLastError();
		} else {
			$result['message'] = "";
		}
		return $result;
	}
	
	public function receiveFile($id_e, $id_d,$field_name,$file_number){
		$document = $this->objectInstancier->Document;
		$info = $document->getInfo($id_d);
		$this->verifDroit($id_e, "{$info['type']}:lecture");
		$donneesFormulaire = $this->objectInstancier->DonneesFormulaireFactory->get($id_d);
		$result['file_name'] = $donneesFormulaire->getFileName($field_name,$file_number);
		$result['file_content'] = $donneesFormulaire->getFileContent($field_name,$file_number);
		return $result;
	}
	
	public function action($id_e, $id_d,$action,$id_destinataire = array(), $action_params=array()){
		$this->setDefault($id_destinataire,array());
		$document = $this->objectInstancier->Document;
		$info = $document->getInfo($id_d);
		$this->verifDroit($id_e, "{$info['type']}:edition");
		
		$actionPossible = $this->objectInstancier->ActionPossible;
		
		if ( ! $actionPossible->isActionPossible($id_e,$this->id_u,$id_d,$action)) {
			throw new Exception("L'action « $action »  n'est pas permise : " .$actionPossible->getLastBadRule());
		}
		
		$result = $this->objectInstancier->ActionExecutorFactory->executeOnDocument($id_e,$this->id_u,$id_d,$action,$id_destinataire, true,$action_params);
		$message = $this->objectInstancier->ActionExecutorFactory->getLastMessage();
		
		if ($result){
			return array("result" => $result,"message"=>$message);
		} else {
			return $this->getError($message);
		}
	}
// Api Provisioning>        
        public function createUtilisateur($data, $fileUploader=null) {
            
            if (isset($data['id_e'])) {
                $id_e = $data['id_e'];
            } else {
                $id_e = 0;
            }
			
            // Vérification des droits.             
            $this->verifDroit($id_e, "utilisateur:edition");
			
            if ($fileUploader) {
                $certificat_content = $fileUploader->getFileContent('certificat');
            }
            
            $id_u_cree = $this->objectInstancier->UtilisateurControler->editionUtilisateur($id_e, null, $data['email'], $data['login'], $data['password'], $data['password'], $data['nom'], $data['prenom'], $certificat_content);   
            $info['id_u']= $id_u_cree;
            return $info;
    
        }
        
        public function modifUtilisateur($data, $fileUploader = null) {
			$utilisateur = $this->objectInstancier->Utilisateur;
			
			// Possibilité de créer un utilisateur si celui ci n'existe pas
			$createUtilisateur = isset($data['create']) ? $data['create'] : FALSE;
			
			// Recherche de l'utilisateur par son identifiant
			if(isset($data['id_u'])) {
				$id_u_a_modifier = $data['id_u'];
				// Chargement de l'utilisateur en base de données
				$infoUtilisateurExistant = $utilisateur->getInfo($id_u_a_modifier);
				if (!$infoUtilisateurExistant) {
					throw new Exception("L'identifiant de l'utilisateur n'existe pas : {id_u=$id_u_a_modifier}");
				}
			}
			// Recherche de l'utilisateur par son login
			elseif(isset($data['login'])) {
				$login = $data['login'];
				// Chargement de l'utilisateur en base de données
				$infoUtilisateurExistant = $utilisateur->getInfoByLogin($login);
				
				// Si l'utilisateur n'existe pas et que l'on n'a pas spécifié vouloir le créer
				if (!$infoUtilisateurExistant && !$createUtilisateur) {
					throw new Exception("Le login de l'utilisateur n'existe pas : {login=$login}");
				}
				$id_u_a_modifier = $infoUtilisateurExistant['id_u'];
			}
			// Impossible de rechercher l'utilisateur sans son identifiant ni son login
			else {
				throw new Exception("Aucun paramètre permettant la recherche de l'utilisateur n'a été renseigné");
			}
			
			// Si l'utilisateur n'existe pas et qu'on a spécifié vouloir le créer
			if(!$infoUtilisateurExistant && $createUtilisateur) {
				return $this->createUtilisateur($data, $fileUploader);
			}
            $id_e = $infoUtilisateurExistant["id_e"];
            
            // Vérification des droits.                         
            $this->verifDroit($id_e, "utilisateur:edition");
            
            
            // Modification de l'utilisateur chargé avec les infos passées par l'API
            foreach ($data as $key => $newValeur) {
                if (array_key_exists($key, $infoUtilisateurExistant)) {
                    $infoUtilisateurExistant[$key] = $newValeur;
                }
            }

            $login = $infoUtilisateurExistant['login'];
            $password = $infoUtilisateurExistant['password'];
            $password2 = $infoUtilisateurExistant['password'];
            $nom = $infoUtilisateurExistant['nom'];
            $prenom = $infoUtilisateurExistant['prenom'];
            $email = $infoUtilisateurExistant['email'];

            if ($fileUploader) {
                $certificat_content = $fileUploader->getFileContent('certificat');
            }

            // Appel du service métier pour enregistrer la modification de l'utilisateur
            $id_u_modifie = $this->objectInstancier->UtilisateurControler->editionUtilisateur($id_e, $id_u_a_modifier, $email, $login, $password, $password2, $nom, $prenom, $certificat_content);

            // Si le certificat n'est pas passé, il faut le supprimer de l'utilisateur
            // Faut-il garder ce comportement ou faire des webservices dédiés à la gestion des certificats (au moins la suppression) ?
            if (!$certificat_content) {
                $utilisateur->removeCertificat($id_u_a_modifier);
            }

            $result['result'] = self::RESULT_OK;
            return $result;
        }
        
        public function detailUtilisateur($id_u) {
            
            // Chargement de l'utilisateur en base de données    
            $infoUtilisateur = $this->objectInstancier->Utilisateur->getInfo($id_u);
            
            // Chargement de l'utilisateur en base de données                
            if (!$infoUtilisateur) {
                throw new Exception("L'utilisateur n'existe pas : {id_u=$id_u}");
            }
            
            // Vérification des droits. 
            $this->verifDroit($infoUtilisateur['id_e'], "utilisateur:lecture");      


            // Création d'un nouveau tableau pour ne retourner que les valeurs retenues
            $result = array();
            $result['id_u'] = $infoUtilisateur['id_u'];
            $result['login'] = $infoUtilisateur['login'];
            $result['nom'] = $infoUtilisateur['nom'];
            $result['prenom'] = $infoUtilisateur['prenom'];
            $result['email'] = $infoUtilisateur['email'];
            $result['certificat'] = $infoUtilisateur['certificat'];
            $result['id_e'] = $infoUtilisateur['id_e'];

            return $result;
        }
        
        public function deleteUtilisateur($data) {
          
            // Chargement de l'utilisateur
            $utilisateurModel = $this->objectInstancier->Utilisateur;
			
			// Recherche de l'utilisateur par son identifiant
			if(isset($data['id_u'])) {
				$id_u = $data['id_u'];

				$infoUtilisateur = $utilisateurModel->getInfo($id_u);
				
				if (!$infoUtilisateur) {
					throw new Exception("L'identifiant de l'utilisateur n'existe pas : {id_u=$id_u}");
				}
			}
			// Recherche de l'utilisateur par son login
			elseif(isset($data['login'])) {
				$login = $data['login'];

				$infoUtilisateur = $utilisateurModel->getInfoByLogin($login);
				
				if (!$infoUtilisateur) {
					throw new Exception("Le login de l'utilisateur n'existe pas : {login=$login}");
				}
				$id_u = $infoUtilisateur['id_u'];
			}
			// Aucun paramètre renseigné
			else {
				throw new Exception("Aucun paramètre n'a été renseigné");
			}       
            
            // Vérification des droits. 
            $this->verifDroit($infoUtilisateur['id_e'], "utilisateur:edition"); 
                        
            // Suppression des données
            $this->objectInstancier->RoleUtilisateur->removeAllRole($id_u);
            $utilisateurModel->desinscription($id_u);
        
            $result['result'] = self::RESULT_OK;
            return $result;
        }
        
        public function listUtilisateur($id_e) {
            // Vérification des droits. 
            if (!$id_e) {
                $id_e=0;
            }
            $this->verifDroit($id_e, "utilisateur:lecture");
            // Chargement de l'utilisateur en base de données    
            $listUtilisateur = $this->objectInstancier->UtilisateurListe->getAllUtilisateurSimple($id_e);
            $result=array();
            if ($listUtilisateur) {
                // Création d'un nouveau tableau pour ne retourner que les valeurs retenues
                foreach($listUtilisateur as $id_u => $utilisateur) {		
                    $result[$id_u] = array('id_u' => $utilisateur['id_u'], 'login' => $utilisateur['login'], 'email' => $utilisateur['email']);        
                }
            }       
            return $result;
        }
        
        public function addRoleUtilisateur ($id_u, $role, $id_e) {
            // Vérification des droits. 
            $this->verifDroit($id_e, "utilisateur:edition");     
              
            if(!$this->objectInstancier->Utilisateur->getInfo($id_u)) {
                throw new Exception("L'utilisateur spécifié n'existe pas {id_u=$id_u}");
            }
                
            if (!$this->objectInstancier->RoleSQL->getInfo($role)) {
                throw new Exception("Le role spécifié n'existe pas {role=$role}");
            }
			if(!$this->objectInstancier->RoleUtilisateur->hasRole($id_u,$role,$id_e)) {
				$this->objectInstancier->RoleUtilisateur->addRole($id_u,$role,$id_e);   
			}
    
            $result['result'] = self::RESULT_OK;
            return $result;
        }
		
		public function addSeveralRolesUtilisateur($data) {
			$infoUtilisateurExistant = $this->getUserFromData($data);
			$infoEntiteExistante = $this->getEntiteFromData($data);
			
			// Possibilité de supprimer les anciens roles avant d'ajouter les nouveaux
			$deleteRoles = isset($data['deleteRoles']) ? $data['deleteRoles'] : FALSE;
			
			if(isset($data['role'])) {
				$roles = $data['role'];
				$id_e = $infoEntiteExistante['id_e'];
				$id_u = $infoUtilisateurExistant['id_u'];
				
				//Suppression des anciens roles
				if($deleteRoles) {
					$this->deleteRoleUtilisateur($id_u, 'ALL_ROLES', $id_e);
				}
				
				if(is_array($roles)) {
					$result = array();
					foreach($roles as $role) {
						// Réception d'un role avec un accent
						$role = utf8_decode($role);
						$result[] = $this->addRoleUtilisateur($id_u, $role, $id_e);
					} 
				}
				else {
					$roles = utf8_decode($roles);
					$result = $this->addRoleUtilisateur($id_u, $roles, $id_e);
				}
				return $result;
			}
		}
		       
        public function createEntite($data) {
            
            // Si l'entité mère n'est pas renseignée, on se positionne sur l'identité racine (id_e=0)
			$entite_mere = isset($data['entite_mere']) ? $data['entite_mere'] : 0;

            // Vérification des droits. 
            $this->verifDroit($entite_mere, "entite:edition");
                        
            $type = $data['type'];
            $siren = $data['siren'];
            $denomination = $data['denomination'];
			$centre_de_gestion = isset($data['centre_de_gestion']) ? $data['centre_de_gestion'] : 0;
            
            $id_e_cree = $this->objectInstancier->EntiteControler->edition(null, $denomination, $siren, $type, $entite_mere, $centre_de_gestion, 'non', 'non');
    
            $info['id_e']= $id_e_cree;
            return $info;
        }
        
        public function deleteEntite($data) {            
            // Chargement de l'entité depuis la base de données
			$entiteSQL = $this->objectInstancier->EntiteSQL;
			$infoEntiteExistante = $this->getEntiteFromData($data);
			$id_e = $infoEntiteExistante['id_e'];
            // Vérification des droits
            $this->verifDroit($id_e, "entite:edition");
                    
            $entiteSQL->removeEntite($id_e);
    
            $result['result'] = self::RESULT_OK;
            return $result;            
        }
        
        public function deleteRoleUtilisateur($id_u, $role, $id_e) {
			$allRoles = "ALL_ROLES";
            // Vérification des droits
            $this->verifDroit($id_e, "utilisateur:edition");
                        
            if(!$this->objectInstancier->Utilisateur->getInfo($id_u)) {
                throw new Exception("L'utilisateur spécifié n'existe pas {id_u=$id_u}");
            }
			//Supprime tous les roles de l'utilisateur pour cette entité
			if($role === $allRoles) {
				$this->objectInstancier->RoleUtilisateur->removeAllRolesEntite($id_u,$id_e);   
			}
			else {
				if (!$this->objectInstancier->RoleSQL->getInfo($role)) {
					throw new Exception("Le role spécifié n'existe pas {role=$role}");
				}
		
				$this->objectInstancier->RoleUtilisateur->removeRole($id_u,$role,$id_e);   
            }
    
            $result['result'] = self::RESULT_OK;
            return $result;
        }
		
		public function deleteSeveralRolesUtilisateur($data) {
			$infoUtilisateurExistant = $this->getUserFromData($data);
			$infoEntiteExistante = $this->getEntiteFromData($data);
			
			if(isset($data['role'])) {
				$roles = $data['role'];
				$id_e = $infoEntiteExistante['id_e'];
				$id_u = $infoUtilisateurExistant['id_u'];
				
				if(is_array($roles)) {
					$result = array();
					foreach($roles as $role) {
						// Réception d'un role avec un accent
						$role = utf8_decode($role);
						$result[] = $this->deleteRoleUtilisateur($id_u, $role, $id_e);
					} 
				}
				else {
					$roles = utf8_decode($roles);
					$result = $this->deleteRoleUtilisateur($id_u, $roles, $id_e);
				}
				return $result;
			}
		}
		
        public function detailEntite ($id_e) {
            
            // Chargement de l'entité depuis la base de données        
            $entiteSQL = $this->objectInstancier->EntiteSQL;
            $infoEntite = $entiteSQL->getInfo($id_e);
    
            if (!$infoEntite) {
                throw new Exception("L'entité n'existe pas : {id_e=$id_e}");
            }

            // Vérification des droits. 
            $this->verifDroit($id_e, "entite:lecture");
        
            // Chargement des entités filles
            $resultFille = array();
            $entiteFille = $entiteSQL->getFille($id_e);
            if ($entiteFille) { 
                //GDON : completer les TU pour passer dans la boucle.
                foreach($entiteFille as $key => $valeur) {
                    $resultFille[$key] = array('id_e' => $valeur['id_e']);
                }
            }
    
            // Construction du tableau resultat
            $result=array();
            $result['id_e'] = $infoEntite['id_e'];
            $result['denomination'] = $infoEntite['denomination'];
            $result['siren'] = $infoEntite['siren'];
            $result['type'] = $infoEntite['type'];
            $result['entite_mere'] = $infoEntite['entite_mere'];
            $result['entite_fille'] = $resultFille;
            $result['centre_de_gestion'] = $infoEntite['centre_de_gestion'];
    
            return $result;
        }
        
        public function listRoleUtilisateur($id_e, $id_u) {
            
            if (!$id_e) {
                $id_e=0;
            }
            $this->verifDroit($id_e, "utilisateur:lecture");
                        
            if(!$this->objectInstancier->Utilisateur->getInfo($id_u)) {
                throw new Exception("L'utilisateur spécifié n'existe pas {id_u=$id_u}");
            }        
    
            $roleUtil = $this->objectInstancier->RoleUtilisateur->getRole($id_u);
            // Construction du tableau de retour
            $result=array();
            foreach ($roleUtil as $id_u_role => $roleU) {        
                $result[$id_u_role] = array('id_u' => $roleU['id_u'], 'role' => $roleU['role'], 'id_e' => $roleU['id_e']);
            }
    
            return $result;
        }
        
        public function modifEntite($data) {
			$entite = $this->objectInstancier->EntiteSQL;
			
			// Possibilité de créer une entité si celle ci n'existe pas
			$createEntite = isset($data['create']) ? $data['create'] : FALSE;
			
			//Recherche de l'entite par son identifiant
			if(isset($data['id_e'])) {
				$id_e = $data['id_e'];
				$infoEntiteExistante = $entite->getInfo($id_e);
				if (!$infoEntiteExistante) {
					throw new Exception("L'identifiant de l'entite n'existe pas : {id_e=$id_e}");
				}
			}
			// Recherche de l'entité par sa dénomination
			elseif(isset($data['denomination'])) {
				$denomination = $data['denomination'];
				$numberOfEntite = $entite->getNumberOfEntiteWithName($denomination);
				
				//Si pas d'entité avec ce nom et que l'on n'a pas choisi de la créer
				if($numberOfEntite == 0 && !$createEntite) {
					throw new Exception("La dénomination de l'entité n'existe pas : {denomination=$denomination}");
				}
				elseif($numberOfEntite > 1) {
					throw new Exception("Plusieurs entités portent le même nom, préférez utiliser son identifiant");
				}
				
				$infoEntiteExistante = $entite->getInfoByDenomination($denomination);
			}
			// Impossible de rechercher l'entité sans son identifiant ni sa dénomination
			else {
				throw new Exception("Aucun paramètre permettant la recherche de l'entité n'a été renseigné");
			}

			// Si l'entite n'existe pas et qu'on a spécifié vouloir la créer
			if(!$infoEntiteExistante && $createEntite) {
				return $this->createEntite($data);
			}
			
			$id_e = $infoEntiteExistante['id_e'];
            // Sauvegarde des valeurs. Si elles ne sont pas présentes dans $data, il faut les conserver.
            $entite_mere = $infoEntiteExistante['entite_mere'];
            $centre_de_gestion = $infoEntiteExistante['centre_de_gestion'];
            
            // Vérification des droits sur l'entité
            $this->verifDroit($id_e, "entite:edition");
            // Vérification des droits sur l'entité mère
            if (array_key_exists("entite_mere", $data) && $data['entite_mere']) {
                $this->verifDroit($data['entite_mere'], "entite:edition");
            }
        
            // Modification de l'entité chargée avec les infos passées par l'API
            foreach($data as $key => $newValeur){
                if (array_key_exists($key, $infoEntiteExistante)) {
                    $infoEntiteExistante[$key] = $newValeur;
                }
            }
    
            $type = $infoEntiteExistante['type'];
            $siren = $infoEntiteExistante['siren'];
            $denomination = $infoEntiteExistante['denomination'];
            if ($infoEntiteExistante['entite_mere']) {
                $entite_mere = $infoEntiteExistante['entite_mere'];
            } 
            if ($infoEntiteExistante['centre_de_gestion']) {
                $centre_de_gestion = $infoEntiteExistante['centre_de_gestion'];
            }
          
            $id_e_modifie = $this->objectInstancier->EntiteControler->edition($id_e, $denomination, $siren, $type, $entite_mere, $centre_de_gestion, 'non', 'non');
    
            $result['result'] = self::RESULT_OK;
            return $result;
    
        }
        
        public function createConnecteurEntite($id_e, $id_connecteur, $libelle) {
            // Vérification des droits
            $this->verifDroit($id_e, "entite:edition");            
            
            $id_ce = $this->objectInstancier->ConnecteurControler->nouveau($id_e, $id_connecteur, $libelle);
            $result['id_ce'] = $id_ce;
            return $result;
        }
        
        public function deleteConnecteurEntite($id_e, $id_ce) {
            // Vérification des droits
            $this->verifDroit($id_e, "entite:edition");            
            
            $id_ce = $this->objectInstancier->ConnecteurControler->delete($id_ce);
            $result['result'] = self::RESULT_OK;
            return $result;
        }
        
        public function modifConnecteurEntite($id_e, $id_ce, $libelle) {
            // Vérification des droits
            $this->verifDroit($id_e, "entite:edition");
            
            $this->objectInstancier->ConnecteurControler->editionLibelle($id_ce, $libelle);
            $result['result']=self::RESULT_OK;
            return $result;
        }
        
        public function detailConnecteurEntite($id_e, $id_ce) {
            $this->verifDroit($id_e, "entite:lecture");
                                             
            $result = $this->objectInstancier->ConnecteurEntiteSQL->getInfo($id_ce);            
            
            if (!$result) {
                throw new Exception("Le connecteur n'existe pas.");
            }
            
            $donneesFormulaire = $this->objectInstancier->DonneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
                        	
            $result['data'] = $donneesFormulaire->getRawData();
	    $result['action-possible'] = $this->objectInstancier->ActionPossible->getActionPossibleOnConnecteur($id_ce, $this->id_u);

            return $result;
        }                
        
        public function listConnecteurEntite($id_e) {
            $this->verifDroit($id_e, "entite:lecture");
            
            $result = $this->objectInstancier->ConnecteurEntiteSQL->getAll($id_e);
            return $result;
        }
        
        public function createFluxConnecteur($id_e, $flux, $type, $id_ce) {
            $this->verifDroit($id_e, "entite:edition");
            
            $id_fe = $this->objectInstancier->FluxControler->editionModif($id_e, $flux, $type, $id_ce);
            
            $result['id_fe'] = $id_fe; 
            return $result;
        }
        
        public function deleteFluxConnecteur($id_e, $id_fe) {            
            
            $this->verifDroit($id_e, "entite:edition");
            
            $fluxEntiteSQL = $this->objectInstancier->FluxEntiteSQL;
            $infoFluxConnecteur = $fluxEntiteSQL->getConnecteurById($id_fe);
    
            if (!$infoFluxConnecteur) {
                throw new Exception("Le connecteur-flux n'existe pas : {id_fe=$id_fe}");
            } else {
                if ($id_e != $infoFluxConnecteur['id_e']) {
                    throw new Exception("Le connecteur-flux n'existe pas sur l'entité spécifié : {id_fe=$id_fe, id_e=$id_e}");
                }
            }
                                            
            $fluxEntiteSQL->removeConnecteur($id_fe);
    
            $result['result'] = self::RESULT_OK;
            return $result;            
        }
        
        
        public function listFluxConnecteur($id_e, $flux=null, $type=null) {
            $this->verifDroit($id_e, "entite:lecture");
            
            $result = $this->objectInstancier->FluxEntiteSQL->getAllFluxEntite($id_e, $flux, $type);                        
            return $result;
        }
        
        public function editConnecteurEntite($data, $fileUploader) {
            $id_e = $data['id_e'];
            $id_ce = $data['id_ce'];
            
            $this->verifDroit($id_e, "entite:edition");
            
            unset($data['id_e']);
            unset($data['id_ce']);
            
            $donneesFormulaire = $this->objectInstancier->DonneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
                       
            $donneesFormulaire->setTabDataVerif($data);
            if ($fileUploader) {
                $donneesFormulaire->saveAllFile($fileUploader);
            } 
            
            foreach($donneesFormulaire->getOnChangeAction() as $action) {	
                $resultAction = $this->objectInstancier->ActionExecutorFactory->executeOnConnecteur($id_ce,$this->objectInstancier->Authentification->getId(),$action, true);
            }
            
            $result['result'] = self::RESULT_OK;
            return $result;            
        }
        
        public function actionConnecteurEntite($id_e, $type_connecteur, $flux, $action, $action_params=array()) {
                            
		// La vérification des droits est déléguée au niveau du test sur l'action est-elle possible.
                //$this->verifDroit($id_e, "entite:edition");
		
                $connecteur_info = $this->objectInstancier->FluxEntiteSQL->getConnecteur($id_e, $flux, $type_connecteur);
                                
                if (!$connecteur_info) {
                    throw new Exception("Le connecteur de type $type_connecteur n'existe pas pour le flux $flux.");
                }
                
                $id_ce=$connecteur_info['id_ce'];
                
		$actionPossible = $this->objectInstancier->ActionPossible;
		
		if ( ! $actionPossible->isActionPossibleOnConnecteur($id_ce, $this->id_u, $action)) {
			throw new Exception("L'action « $action »  n'est pas permise : " .$actionPossible->getLastBadRule());
		}
		
		$result = $this->objectInstancier->ActionExecutorFactory->executeOnConnecteur($id_ce,$this->id_u,$action, true, $action_params);
		$message = $this->objectInstancier->ActionExecutorFactory->getLastMessage();
		
		if ($result){
			return array("result" => $result, "message"=>$message);
		} else {
			return $this->getError($message);
		}            
        }        
     
      
      
        public function getInfoConnecteur($id_e, $typeConnecteur, $flux, $methode_name, $params) {
            $this->verifDroit($id_e, "entite:lecture");
            $conn = $this->objectInstancier->ConnecteurFactory->getConnecteurByType($id_e, $flux, $typeConnecteur);
            if (!$conn) {
                throw new Exception ("Aucun connecteur de type $typeConnecteur est défini pour le type $flux.");
            }
            if (!method_exists($conn, $methode_name)) {
                throw new Exception("La méthode $methode_name n'existe pas pour le connecteur.");
            }            
            return call_user_func_array(array($conn, $methode_name), $params);
        }
				
		public function listRoles(){
			$type = "role";
			$roleUtilisateur = $this->objectInstancier->RoleUtilisateur;
			if (!$roleUtilisateur->hasOneDroit($this->id_u, "$type:lecture")) {
				throw new Exception("Acces interdit type=$type,id_u=$this->id_u");
			}
			return $roleUtilisateur->getAllRoles();
		}
		
//Fonctions d'aide
		private function getUserFromData(&$data) {
			$utilisateur = $this->objectInstancier->Utilisateur;
			//Recherche de l'utilisateur par son identifiant
			if(isset($data['id_u'])) {
				$id_u = $data['id_u'];
				$infoUtilisateurExistant = $utilisateur->getInfo($id_u);
				if (!$infoUtilisateurExistant) {
					throw new Exception("L'identifiant de l'utilisateur n'existe pas : {id_u=$id_u}");
				}
			}
			// Recherche de l'utilisateur par son login
			elseif(isset($data['login'])) {
				$login = $data['login'];
				$infoUtilisateurExistant = $utilisateur->getInfoByLogin($login);
				
				if (!$infoUtilisateurExistant) {
					throw new Exception("Le login de l'utilisateur n'existe pas : {login=$login}");
				}
			}
			// Impossible de rechercher l'utilisateur sans son identifiant ni son login
			else {
				throw new Exception("Aucun paramètre permettant la recherche de l'utilisateur n'a été renseigné");
			}
			
			return $infoUtilisateurExistant;
		}
		
		private function getEntiteFromData(&$data) {
			$entite = $this->objectInstancier->EntiteSQL;
			//Recherche de l'entite par son identifiant
			if(isset($data['id_e'])) {
				$id_e = $data['id_e'];
				$infoEntiteExistante = $entite->getInfo($id_e);
				if (!$infoEntiteExistante) {
					throw new Exception("L'identifiant de l'entite n'existe pas : {id_e=$id_e}");
				}
			}
			// Recherche de l'entité par sa dénomination
			elseif(isset($data['denomination'])) {
				$denomination = $data['denomination'];
				$numberOfEntite = $entite->getNumberOfEntiteWithName($denomination);
				
				if($numberOfEntite == 0) {
					throw new Exception("La dénomination de l'entité n'existe pas : {denomination=$denomination}");
				}
				elseif($numberOfEntite > 1) {
					throw new Exception("Plusieurs entités portent le même nom, préférez utiliser son identifiant");
				}
				//Si une seule entité porte ce nom
				else {
					$infoEntiteExistante = $entite->getInfoByDenomination($denomination);
				}
			}
			// Impossible de rechercher l'entité sans son identifiant ni sa dénomination
			else {
				throw new Exception("Aucun paramètre permettant la recherche de l'entité n'a été renseigné");
			}
			
			return $infoEntiteExistante;
		}


	public function editExtension($id_extension,$path){
		$result['detail_extension'] = $this->objectInstancier->SystemControler->doExtensionEdition($id_extension,$path);
		$result['result'] = self::RESULT_OK;
		return $result;
	}

	public function listExtension(){
		$result['result'] = $this->objectInstancier->SystemControler->extensionList();
		return $result;
	}

	public function deleteExtension($id_extension){
		$this->objectInstancier->SystemControler->extensionDelete($id_extension);
		$result['result'] = self::RESULT_OK;
		return $result;
	}


}
