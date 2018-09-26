<?php 

class IParapheur extends SignatureConnecteur {
	
	const IPARAPHEUR_NB_JOUR_MAX_DEFAULT = 30;
    
	const ARCHIVAGE_ACTION_EFFACER = "EFFACER";
	const ARCHIVAGE_ACTION_ARCHIVER = "ARCHIVER";

    const ARCHIVAGE_ACTION_DEFAULT = self::ARCHIVAGE_ACTION_EFFACER;

    private $wsdl;
	private $userCert;
	private $userCertPassword;
	private $login_http;
	private $password_http;
	
	private $userKeyOnly;
	private $userCertOnly;
	
	private $iparapheur_type;
	private $iparapheur_nb_jour_max;
	private $visibilite;
	private $xPathPourSignatureXML;

	private $soapClientFactory;

	private $activate;

	/** @var NotBuggySoapClient */
	private $last_client;

	private $iparapheur_metadata;
	private $sending_metadata;
	private $iparapheur_archivage_action;
	
	public function __construct(SoapClientFactory $soapClientFactory){
		$this->soapClientFactory = $soapClientFactory;
	}
	
	public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties){
		$this->wsdl = $collectiviteProperties->get("iparapheur_wsdl");
		$this->activate = $collectiviteProperties->get("iparapheur_activate");
		$this->userCert = $collectiviteProperties->getFilePath("iparapheur_user_key_pem");
		$this->userCertPassword = $collectiviteProperties->get("iparapheur_user_certificat_password");
		$this->login_http = $collectiviteProperties->get("iparapheur_login");
		$this->password_http = $collectiviteProperties->get("iparapheur_password");
		
		$this->userKeyOnly = $collectiviteProperties->getFilePath("iparapheur_user_key_only_pem");
		$this->userCertOnly = $collectiviteProperties->getFilePath("iparapheur_user_certificat_pem");
		$this->iparapheur_type = $collectiviteProperties->get("iparapheur_type");
		$this->iparapheur_nb_jour_max = $collectiviteProperties->get("iparapheur_nb_jour_max");
		
		$this->visibilite = $collectiviteProperties->get('iparapheur_visibilite')?:"SERVICE";
		
		$this->xPathPourSignatureXML =  $collectiviteProperties->get('XPathPourSignatureXML');
        $this->iparapheur_metadata =  $collectiviteProperties->get('iparapheur_metadata');
        $iparapheur_archivage_action = $collectiviteProperties->get('iparapheur_archivage_action');

        if (! in_array(
            $iparapheur_archivage_action,[self::ARCHIVAGE_ACTION_EFFACER,self::ARCHIVAGE_ACTION_ARCHIVER])
        ){
            $iparapheur_archivage_action = self::ARCHIVAGE_ACTION_DEFAULT;
        }
        $this->iparapheur_archivage_action = $iparapheur_archivage_action;
    }
	
	public function getNbJourMaxInConnecteur(){		
		if ($this->iparapheur_nb_jour_max){
			return $this->iparapheur_nb_jour_max;
		}
		return self::IPARAPHEUR_NB_JOUR_MAX_DEFAULT;
	}
	
	
	public function getDossierID($id,$name){
		$name = preg_replace("#[^A-Za-z0-9éèçàêîâôûùüÉÈÇÀÊÎÂÔÛÙÜ_]#u", "_", $name);
		$name=substr($name,0,100);
		return "$id $name";
	}

	/**
	 * @param $dossierID
	 * @return mixed
	 * @throws Exception
	 */
	public function getDossier($dossierID){
		return  $this->getClient()->GetDossier($dossierID);
	}


	public function getBordereau($result){
		$info = array();
		if (! isset($result->DocumentsAnnexes)){
			$info['document'] = false;
			$info['nom_document'] = false;
			return $info;
		}
		
		if (isset($result->DocumentsAnnexes->DocAnnexe->fichier)){
			$info['document'] = $result->DocumentsAnnexes->DocAnnexe->fichier->_;
			$info['nom_document'] = trim($result->DocumentsAnnexes->DocAnnexe->nom, '"');
			return $info;
		} 
		
		foreach($result->DocumentsAnnexes->DocAnnexe as $bordereau){}
		$info['document'] = $bordereau->fichier->_;
		$info['nom_document'] = trim($bordereau->nom, '"');
		return $info;
	}

	public function getAnnexe($result){

		if (! isset($result->DocumentsAnnexes->DocAnnexe)){
			return [];
		}

		$all_doc_annexe = $result->DocumentsAnnexes->DocAnnexe ;

		if (count($all_doc_annexe)<2){
			return [];
		}

		$result = [];

		// Le dernier document est forcément le bordereau
		array_pop($all_doc_annexe);

		foreach($all_doc_annexe as $annexe){
			$result[] = [
				'nom_document' => trim($annexe->nom, '"'),
				'document' => $annexe->fichier->_
				];
		}
		return $result;
	}

	/**
	 * @param array $info_from_get_signature output of IParapheur::getSignature()
	 * @param int $ignore_count Ignore the $ignore_count first annexe (i-Parapheur send back the annexes created initialy)
	 * @return array output annexe
	 */
	public function getOutputAnnexe(array $info_from_get_signature,int $ignore_count){
		if (empty($info_from_get_signature['annexe'])){
			return [];
		}
		return array_slice($info_from_get_signature['annexe'],$ignore_count);
	}
	
	private function getDocumentSigne($result){
		$info = array();
		if (! isset($result->DocPrincipal)){
			$info['document'] = false;
			$info['nom_document'] = false;
			return $info;
		}
		$info['document'] = $result->DocPrincipal->_;
		$info['nom_document'] = $result->NomDocPrincipal;
		return $info;
	}

    public function getAllMetaDonnees($result){
        $info = array();
        if (! isset($result->MetaDonnees)){
            return false;
        }

        $array_metadonnees = json_decode(json_encode($result->MetaDonnees),true);

        foreach($array_metadonnees as $metadonnee) {
            if (isset($metadonnee['nom'])){
                $info[] = [
                    "nom" => $metadonnee["nom"],
                    "valeur" => $metadonnee["valeur"],
                ];
            } else {
                foreach ($metadonnee as $value) {
                    if (isset($value['nom'])) {
                        $info[] = [
                            "nom" => $value["nom"],
                            "valeur" => $value["valeur"],
                        ];
                    }
                }
            }
        }
        return $info;
    }

    public function getMetaDonnee($metaDonnees, $nom){
        if ($metaDonnees){
            foreach ($metaDonnees as $metaDonnee) {
                if (($metaDonnee["nom"]) == $nom){
                    return $metaDonnee["valeur"];
                }
            }
        }
        return false;
    }

    /**
     * @param $dossierID
     * @param bool $archiver => Il faut toujours mettre false et appellé archiver() après avoir enregistré la signature
     *                  Sinon, en cas de fulldisk, on perd la signature et le parapheur l'a effacé !
     *                  Il faudrait refaire cette fonction...
     * @return array|bool
     */
	public function getSignature($dossierID,$archiver = true){
		try{
			$result =  $this->getClient()->GetDossier($dossierID);
			if ($result->MessageRetour->codeRetour != 'OK'){
				$message = "[{$result->MessageRetour->severite}] {$result->MessageRetour->message}";
				$this->lastError = $message;
				return false;
			}
			$info = $this->getBordereau($result);
            $info['meta_donnees'] = $this->getAllMetaDonnees($result);
			
			if (isset($result->SignatureDocPrincipal)){
				$info['signature'] = $result->SignatureDocPrincipal->_;
			} elseif (isset($result->FichierPES)) {
				$info['signature'] = $result->FichierPES->_;
			} else {
				$info['signature'] = false;
			}
			
			$info['document_signe'] = $this->getDocumentSigne($result);

			$info['annexe'] = $this->getAnnexe($result);

			if ($archiver) {
			    //TODO BUG ! Si on fait ca et qu'on arrive pas à écrire sur le FS, alors... on est mal...
				$this->archiver($dossierID);
			}
			return $info;
		} catch (Exception $e){
		 	$this->lastError = "Erreur sur la récupération de la signature : ".$e->getMessage();
			return false;			
		}
	}
	
	public function archiver($dossierID){
		try {
			$this->getLogger()->debug(
			    "Archivage  ( $this->iparapheur_archivage_action) du dossier $dossierID sur le i-parapheur"
            );

			$result = $this->getClient()->ArchiverDossier([
			    "DossierID" => $dossierID,
                "ArchivageAction" => $this->iparapheur_archivage_action
            ]);
			$this->getLogger()->debug("Réponse de l'archivage du dossier $dossierID: ".json_encode($result));
			if (empty($result->MessageRetour->codeRetour) || $result->MessageRetour->codeRetour != 'OK'){
				$this->lastError = "Impossible d'archiver le dossier $dossierID sur le i-Parapheur : ".json_encode($result);
				$this->getLogger()->notice($this->lastError);
				return false;
			}
		} catch(Exception $e){
			$this->lastError = $e->getMessage();
			return false;
		}
		return $result;
	}
	
	public function effacerDossierRejete($dossierID){
		try {
			$result = $this->getClient()->EffacerDossierRejete($dossierID);
		} catch(Exception $e){
			$this->lastError = $e->getMessage();
			return false;
		}
		return $result;
	}

	public function ExercerDroitRemordDossier($dossierID){
		$info = array();
		try{
			$result =  $this->getClient()->ExercerDroitRemordDossier($dossierID);
			$info["codeRetour"]=$result->MessageRetour->codeRetour;
			$info["message"]=$result->MessageRetour->message;
			$info["severite"]=$result->MessageRetour->severite;
			if ($info["codeRetour"] == 'OK'){
				$this->archiver($dossierID);
			}
			return $info;
		} catch (Exception $e){
			$this->lastError = "Erreur sur le droit de remord du dossier iParapheur : ".$e->getMessage();
			return false;
		}

	}
	
	public function getAllHistoriqueInfo($dossierID){
		try{
			$result =  $this->getClient()->GetHistoDossier($dossierID);
			if ( empty($result->LogDossier)){
				$this->lastError = "Le dossier n'a pas été trouvé";
				return false;
			}
			return $result;
		}  catch (Exception $e){
			$this->lastError = $e->getMessage();
			return false;			
		}
	}
	
	public function getLastHistorique($all_historique){
		$lastLog = end($all_historique->LogDossier);
		$date = date("d/m/Y H:i:s",strtotime($lastLog->timestamp));
		return $date . " : [" . $lastLog->status . "] ".$lastLog->annotation;
	}
	
	public function getHistorique($dossierID){
		try{
			$result =  $this->getClient()->GetHistoDossier($dossierID);
			
			if ( empty($result->LogDossier)){
				$this->lastError = "Le dossier n'a pas été trouvé";
				return false;
			}
			return $this->getLastHistorique($result);
		}  catch (Exception $e){
			$this->lastError = $e->getMessage();
			return false;			
		}
	}

	public function setSendingMetadata(DonneesFormulaire $donneesFormulaire){
        $all_metadata = explode(",",$this->iparapheur_metadata);
        $result = array();
        foreach($all_metadata as $metadata_association){
            $data = explode(":",$metadata_association);
            if (count($data)<2){
                continue;
            }
            $element_pastell = $data[0];
            $metadata_parapheur = $data[1];
            if ($element_pastell && $metadata_parapheur){
                $result[$metadata_parapheur] = $donneesFormulaire->get($element_pastell);
            }
        }

        $this->sending_metadata = $result;
    }

    public function getSendingMetadata(){
	    return $this->sending_metadata;
    }

    public function sendHeliosDocument(
        $typeTechnique,
        $sousType,
        $dossierID,
        $document_content,
        $content_type,
        $visuel_pdf,
        array $metadata = array()
    ){
		try {
			$client = $this->getClient();	
			$data = array(
					"TypeTechnique"=>$typeTechnique,
					"SousType"=> $sousType,
					"DossierID" => $dossierID,
					"DocumentPrincipal" => array("_"=>$document_content,"contentType"=>$content_type),
					"VisuelPDF" => array("_" => $visuel_pdf, "contentType" => "application/pdf"),
					"Visibilite" => $this->visibilite,
					"XPathPourSignatureXML" => $this->getXPathPourSignatureXML($document_content),
					
			);

            if ($this->sending_metadata){
                $metadata = $this->sending_metadata;
            }

            if ($metadata) {
                $data['MetaData'] = array('MetaDonnee' => array());

                foreach($metadata as $nom => $valeur){
                    $data['MetaData']['MetaDonnee'][] = array('nom'=>$nom,'valeur'=>$valeur);
                }
            }


            $result =  $client->CreerDossier($data);

			$messageRetour = $result->MessageRetour;
			$message = "[{$messageRetour->severite}] {$messageRetour->message}";
			if ($messageRetour->codeRetour == "KO"){
				$this->lastError = $message;
				return false;
			} elseif($messageRetour->codeRetour == "OK") {
				return $message;
			} else {
				$this->lastError = "Le iparapheur n'a pas retourné de code de retour : " . $message;
				return false;
			}		
		} catch (Exception $e){
			$this->lastError = $e->getMessage() ;
			if (! empty($client)){
				$this->lastError .= $client->__getLastResponse();
			} 
			return false;			
		}
		
	}
	
	
	public function sendDocument(
		$typeTechnique,
		$sousType,
		$dossierID,
		$document_content,
		$content_type,
		array $all_annexes = array(),
		$date_limite = false,
		$visuelPDFContent = "",
		$xPathPourSignatureXML = false,
		$annotationPublic = "",
		$annotationPrivee = "",
		$emailEmetteur = "",
        $metadata = array(),
        $dossierTitre = "",
        $signature_content = "",
        $signature_type = ""
	) {

		try {
			$client = $this->getClient();

			$data = array(
						"TypeTechnique"=>$typeTechnique,
						"SousType"=> $sousType,
						"DossierID" => $dossierID,
						"DocumentPrincipal" => array("_"=>$document_content,"contentType"=>$content_type),
						"Visibilite" => $this->visibilite,
				);


            if ($signature_content){
                $data['SignatureDocPrincipal'] = array("_"=>$signature_content,"contentType"=>$signature_type);
            }

            if ($dossierTitre){
                $data['DossierTitre'] = $dossierTitre;
            }

			if ($date_limite) {
				$data['DateLimite'] = $date_limite;
			}
			if ($all_annexes){
				$data["DocumentsAnnexes"] = array();
			}

			if ($visuelPDFContent){
				$data["VisuelPDF"] = array("_" => $visuelPDFContent, "contentType" => "application/pdf");
			}

			if ($xPathPourSignatureXML){
				$data["XPathPourSignatureXML"] = $xPathPourSignatureXML;
			}

			if ($annotationPublic){
				$data["AnnotationPublique"] = $annotationPublic;
			}

			if ($annotationPrivee){
				$data["AnnotationPrivee"] = $annotationPrivee;
			}

			if ($emailEmetteur){
				$data["EmailEmetteur"] = $emailEmetteur;
			}


			foreach($all_annexes as $annexe){
					$data["DocumentsAnnexes"][] = array("nom"=>$annexe['name'],
													"fichier" => array("_"=>$annexe['file_content'],
													"contentType"=>$annexe['content_type']),
													"mimetype" => $annexe['content_type'],
													"encoding" => "UTF-8"
				);
				
			}

            if ($this->sending_metadata){
                $metadata = $this->sending_metadata;
            }

			if ($metadata) {
				$data['MetaData'] = array('MetaDonnee' => array());

				foreach($metadata as $nom => $valeur){
					$data['MetaData']['MetaDonnee'][] = array('nom'=>$nom,'valeur'=>$valeur);
				}
			}

			$result =  $client->CreerDossier($data);

			$messageRetour = $result->MessageRetour;
			$message = "[{$messageRetour->severite}] {$messageRetour->message}";
			if ($messageRetour->codeRetour == "KO"){
				$this->lastError = $message;
				return false;
			} elseif($messageRetour->codeRetour == "OK") {
				return $message;
			} else {
				$this->lastError = "Le iparapheur n'a pas retourné de code de retour : " . $message;
				return false;
			}		
		} catch (Exception $e){

			$this->lastError = $e->getMessage() ;
			if (! empty($client)){
				$this->lastError .= $client->__getLastResponse();
			}
			return false;
		}
		
	}
	
	public function sendDocumentTest(){
		$dossierID = 'TESTACTE_'.mt_rand();
		$sous_type = $this->getSousType();
		$content_pdf  = file_get_contents(__DIR__ ."/data-exemple/exemple.pdf");
		return $this->sendDocument($this->iparapheur_type,$sous_type[0],$dossierID,$content_pdf,"application/pdf");
	}
	
	public function sendDocumentTestHelios(){
		$dossierID = 'TESTHELIOS_'.mt_rand();
		$sous_type = $this->getSousType();
		$content_pdf  = file_get_contents(__DIR__ ."/data-exemple/exemple.pdf");
		$content_xml  = file_get_contents(__DIR__ ."/data-exemple/PES_ex.xml");
		return $this->sendHeliosDocument($this->iparapheur_type,$sous_type[0],$dossierID,$content_xml,"application/xml", $content_pdf);
	}

	/**
	 * @return NotBuggySoapClient
	 * @throws Exception
	 */
	protected function getClient(){
// 		static $client;
// var_dump($client);
// 		if ($client) {
// 			return $client;
// 		}
		if ( ! $this->activate){
			$this->lastError = "Le module n'est pas activé";
			throw new Exception("Le module n'est pas activé");
		}
		if (! $this->wsdl ){
			$this->lastError = "Le WSDL n'a pas été fourni";
			throw new Exception("Le WSDL n'a pas été fourni");
		}

		/*
		 * En PHP 5.6, SoapClient vérifie forcément le peer lors de la récupération du WDSL
		 */
		$stream_context = stream_context_create(
			array(
				"ssl"=>array(
					"verify_peer"=>false,
					"verify_peer_name"=>false
				)
			)
		);


		$client = $this->soapClientFactory->getInstance(
				$this->wsdl,
				array(
	     			'local_cert' => $this->userCert,
	     			'passphrase' => $this->userCertPassword,
					'login' => $this->login_http,
					'password' => $this->password_http,
					'trace' => 1,
					'exceptions' => 1,
					'use_curl' => 1,
					'userKeyOnly' => $this->userKeyOnly,
					'userCertOnly' => $this->userCertOnly,
					"stream_context" => $stream_context
	    		),true);

// echo '<pre>';
// var_dump($client);
// // die();
		$this->last_client = $client;
		return $client;
	} 
	
	public function getType(){
		try{
			$type = $this->getClient()->GetListeTypes()->TypeTechnique;			
			if (is_array($type)){
				foreach($type as $n => $v){
					$result[$n] = $v;
				}
			} else {
				$result[0] = $type;
			}
			sort($result);
			return $result;
		}  catch (Exception $e){
			$this->lastError = $e->getMessage();
			return false;			
		}
	}
	
	public function getSousType(){
		$type = $this->iparapheur_type;
		try{
			$sousType = $this->getClient()->GetListeSousTypes($type)->SousType;
			$result = array();
			if (is_array($sousType)){
				foreach($sousType as $n => $v){
					$result[$n] = $v;
				}
			} else {
				$result[0] = $sousType;
			}
            sort($result);
			return $result;
		}  catch (Exception $e){
			$this->lastError = $e->getMessage();
			return false;
		}
	}
	
	public function testConnexion(){
		$client = $this->getClient();
		return $client->echo("Dès Noël où un zéphyr haï me vêt de glaçons würmiens je dîne d’exquis rôtis de bœuf au kir à l’aÿ d’âge mûr & cætera");
	}
	
	public function getLogin(){
		return $this->login_http;
	}

	/**
	 * @param $pes_content
	 * @return string
	 * @throws Exception
	 */
	public function getXPathPourSignatureXML($pes_content){
		if ($this->xPathPourSignatureXML == 2){
			return "//Bordereau";
		}
		if ($this->xPathPourSignatureXML == 3){
			return ".";
		}
		return $this->getXPathPourSignatureXMLBestMethod($pes_content);
	}

	/**
	 * @param $pes_content
	 * @return string
	 * @throws Exception
	 */
	public function getXPathPourSignatureXMLBestMethod($pes_content){
		$xml = simplexml_load_string($pes_content,'SimpleXMLElement',LIBXML_PARSEHUGE);
	
		if ($this->allBordereauHasId($xml)){
			return "//Bordereau";
		}
		if (! empty($xml['Id'])){
			return ".";			
		}
		
		throw new Exception("Le bordereau du fichier PES ne contient pas d'identifiant valide, ni la balise PESAller : signature impossible");
	}

	/**
	 * @param $simple_xml_pes_content
	 * @return bool
	 * @throws Exception
	 */
	private function allBordereauHasId($simple_xml_pes_content){
		if ($simple_xml_pes_content->PES_DepenseAller){
			$root = $simple_xml_pes_content->PES_DepenseAller;
		} else if($simple_xml_pes_content->PES_RecetteAller) {
			$root = $simple_xml_pes_content->PES_RecetteAller;
		} else {
			throw new Exception("Le bordereau ne contient ni Depense ni Recette");
		}
	
		foreach($root->Bordereau as $bordereau){
			$attr = $bordereau->attributes();
			if (empty($attr['Id'])){
				return false;
			}
		}
		return true;
	}

	public function getLastRequest(){
		$dom = new DOMDocument();
		$dom->loadXML($this->last_client->__getLastRequest());
		$dom->formatOutput = true;
		return $dom->saveXML();
	}
	
	
}
