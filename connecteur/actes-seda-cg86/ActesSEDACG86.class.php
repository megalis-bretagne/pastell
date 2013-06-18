<?php 

//Conforme aux demandes du CG86
//Document utilis� : profil_actes_juin2012.ods

class ActesSEDACG86  extends Connecteur {
	
	private $authorityInfo;
	
	public function  setConnecteurConfig(DonneesFormulaire $seda_config){
		$this->authorityInfo = array(
				"identifiant_versant" =>  $seda_config->get("identifiant_versant"),
				"identifiant_archive" =>  $seda_config->get("identifiant_archive"),
				"sae_numero_aggrement" =>  $seda_config->get("numero_agrement"),
				"originating_agency" =>  $seda_config->get("originating_agency"),
				"nom_entite" =>   $seda_config->get('nom_entite'),
				"siren_entite" =>  $seda_config->get('siren_entite'),
		);
	}
	
	/*****/
	private $tmpFolder;
	private $file2Add;
	
	
	private $actesTransactionsStatusInfo;
	
	private $actesFilePath;
	private $actesIsSigned;
	private $annexe;
	private $arActes;
	
	
	private $relatedTransaction;
	private $latestDate;
	
	private $transfer_identifier;
	
	
	public function setActesFileName($actesFileName,$is_signed = false){
		$this->actesFileName =  $actesFileName;
		$this->file2Add[] = $actesFileName;
		$this->actesIsSigned = $is_signed;
	}
	
	public function setLatestDate($latestDate){
		$this->latestDate = $latestDate;
	}
	
	public function setTransactionStatusInfo(array $actesTransactionsStatusInfo){
		$this->actesTransactionsStatusInfo = $actesTransactionsStatusInfo;
		$this->arActes = "ARActes-{$actesTransactionsStatusInfo['transaction_id']}.xml";
		file_put_contents($this->tmpFolder . $this->arActes,$actesTransactionsStatusInfo['flux_retour']);
		$this->file2Add[] = $this->arActes;
	}
	
	public function addAnnexe($annexeFileName,$annexeFileType,$has_signature){
		$this->annexe[] = array($annexeFileName,$annexeFileType,$has_signature);
		$this->file2Add[] = $annexeFileName;
	}

	public function addRelatedTransaction($relatedTransaction,array $all_file){
		foreach($all_file as $f){
			$this->file2Add[] = $f[0];
			$relatedTransaction['file_name'] = $f[0];
			$relatedTransaction['file_type']= $f[1];
			$relatedTransaction['many_file_name'][] = $f[0];
			$relatedTransaction['many_file_type'][] = $f[1]; 
		}
		$this->relatedTransaction[] = $relatedTransaction;
		if(! empty($relatedTransaction['status_info']['flux_retour'])){
			$ar = "AR1-{$relatedTransaction['id']}.xml";
			file_put_contents($this->tmpFolder . $ar,$relatedTransaction['status_info']['flux_retour']);
			$this->file2Add[] = $ar;
		}
		if(! empty($relatedTransaction['status_info_recu']['flux_retour'])){
			$ar = "AR2-{$relatedTransaction['id']}.xml";
			file_put_contents($this->tmpFolder . $ar,$relatedTransaction['status_info_recu']['flux_retour']);
			$this->file2Add[] = $ar;
		}
	}
	
	public function getArchive(){
		$fileName = uniqid().".tar.gz";
		$command = "tar cvzf {$this->tmpFolder}/$fileName --directory {$this->tmpFolder} " . implode(" ",$this->file2Add);
		$status = exec($command );
		if (! $status){
			$this->lastError = "Impossible de cr�er le fichier d'archive $fileName";
			return false;
		}
		return $this->tmpFolder."$fileName";
	}

	public function calcTransferIdentifier($numero_transfert){
		assert('$this->authorityInfo');
		return $this->authorityInfo['sae_numero_aggrement'] ."-". date("Y-m-d") ."-".$numero_transfert;
	}
	
	public function setTransferIdentifier($transfer_identifier){
		$this->transfer_identifier = $transfer_identifier;
	}
	
	public function checkInformation(array $information){
		$info = array('numero_acte_prefecture','numero_acte_collectivite','subject','decision_date',
					'nature_descr','nature_code','classification',
					'latest_date','actes_file','ar_actes');		
		foreach($info as $key){
			if (empty($information[$key])){
				throw new Exception("Impossible de g�n�rer le bordereau : le param�tre $key est manquant. ");
			}
		}
	}
	
	public function getBordereau($transactionsInfo){
		$this->checkInformation($transactionsInfo);
		
		$archiveTransfer = new ZenXML('ArchiveTransfer');
		$archiveTransfer['xmlns'] = "fr:gouv:ae:archive:draft:standard_echange_v0.2";
		$archiveTransfer->Comment = "Transfert d'un acte soumis au contr�le de l�galit�";
		$archiveTransfer->Date = date('c');//"2011-08-12T11:03:32+02:00";
		$archiveTransfer->TransferIdentifier = $this->transfer_identifier;
		$archiveTransfer->TransferIdentifier['schemeAgencyName'] = "S�LOW - ADULLACT";
		
		$archiveTransfer->TransferringAgency = "####SAE_ID_VERSANT####";
		$archiveTransfer->ArchivalAgency = "####SAE_ID_ARCHIVE####";
		
		if (!empty($this->file2Add)){
			foreach($this->file2Add as $i => $fileName){
				$archiveTransfer->Integrity[$i]->Contains = sha1_file($this->tmpFolder.$fileName);
				$archiveTransfer->Integrity[$i]->Contains['algorithme'] = "http://www.w3.org/2000/09/xmldsig#sha1";
				$archiveTransfer->Integrity[$i]->UnitIdentifier = $fileName;
			}
		}
		
		
		$archiveTransfer->Contains->ArchivalAgreement = $this->authorityInfo['sae_numero_aggrement'];
		$archiveTransfer->Contains->ArchivalAgreement['schemeName'] = "Convention de transfert";
		$archiveTransfer->Contains->ArchivalAgreement['schemeAgencyName'] = "S�LOW - ADULLACT";
		
		$archiveTransfer->Contains->ArchivalProfile = "ACTES";
		$archiveTransfer->Contains->ArchivalProfile['schemeName'] = "Profil de donn�es";
		$archiveTransfer->Contains->ArchivalProfile['schemeAgencyName'] = "Profil �labor� par les Archives d�partementales de l'Aube, valid� par le SIAF et mis en oeuvre sur la plateforme S2LOW.";
		
		$archiveTransfer->Contains->DescriptionLanguage = "fr";
		$archiveTransfer->Contains->DescriptionLanguage['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->DescriptionLevel = "file";
		$archiveTransfer->Contains->DescriptionLevel['listVersionID'] = "edition 2009";
		
		$archiveTransfer->Contains->Name = "Contr�le de l�galit� : " . $transactionsInfo['nature_descr'] . 
											" de ". $this->authorityInfo['nom_entite'] .", en date du " .
											date('d/m/Y',strtotime($transactionsInfo['decision_date'])) .
											", t�l�transmis � la Pr�fecture le " .
											date('d/m/Y',strtotime($this->actesTransactionsStatusInfo['date'])) .".";
		
		$archiveTransfer->Contains->ContentDescription->CustodialHistory = "�Actes d�mat�rialis�s soumis au contr�le de l�galit� t�l�transmis via la plateforme S2LOW de l'ADULLACT pour ".
																				$this->authorityInfo['nom_entite'] . 
																			". Les donn�es archiv�es sont structur�es selon le sch�ma m�tier Actes (Aide au contr�le de l�galit� d�mat�rialis�) �tabli par le Minist�re de l'int�rieur, de l'outre mer et des collectivit�s territoriales. La description a �t� �tablie selon les r�gles du standard d'�change de donn�es pour l'archivage version 0.2";
			
		$archiveTransfer->Contains->ContentDescription->Description = $transactionsInfo['nature_descr'] . " N� ".$transactionsInfo['numero_acte_collectivite'] . 
										" en date du ". date('d/m/Y',strtotime($transactionsInfo['decision_date'])).
										" portant sur : " . $transactionsInfo['subject'];
		
		$archiveTransfer->Contains->ContentDescription->Language = "fr";
		$archiveTransfer->Contains->ContentDescription->Language['listVersionID'] = "edition 2009";
		
		$archiveTransfer->Contains->ContentDescription->LatestDate = date('Y-m-d',strtotime($this->latestDate));
		$archiveTransfer->Contains->ContentDescription->OldestDate = date('Y-m-d',strtotime($transactionsInfo['decision_date']));

		$archiveTransfer->Contains->ContentDescription->OriginatingAgency = "####SAE_ORIGINATING_AGENCY####";
		
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordContent = $this->authorityInfo['nom_entite'];
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordReference = $this->authorityInfo['siren_entite'];
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordReference['schemeName'] = "SIRENE";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordReference['schemeAgencyName'] = "INSEE";	
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType = "corpname";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType["listVersionID"] = "edition 2009";
		
		
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordContent = "Contr�le de l�galit�";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordReference['schemeName'] = "Th�saurus pour la description et l'indexation des archives locales anciennes, modernes et contemporaines_liste d'autorit� Actions";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordReference['schemeAgencyName'] = "Direction des Archives de France";	
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordReference['schemeDataURI'] = "http://www.archivesdefrance.culture.gouv.fr/gerer/classement/normesoutils/thesaurus/";	
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordReference['schemeVersionID'] = "version 2009";			
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordType = "subject";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordType["listVersionID"] = "edition 2009";
		
		
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordContent = $transactionsInfo['nature_descr'];
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordReference = $transactionsInfo['nature_code'];
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordReference['schemeName'] = "ACTES.codeNatureActe";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordReference['schemeAgencyName'] = "Minist�re de l'int�rieur, de l'outre mer et des collectivit�s territoriales";	
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordReference['schemeVersionID'] = "ACTES V1.4";			
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordType = "genreform";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordType["listVersionID"] = "edition 2009";
		
		if ($transactionsInfo['classification'][0] != 9 ){
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordContent = $this->getSujetActes($transactionsInfo['classification']);
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordReference['schemeName'] = "Th�saurus pour la description et l'indexation des archives locales anciennes, modernes et contemporaines_liste d'autorit� Actions";
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordReference['schemeAgencyName'] = "Direction des Archives de France";	
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordReference['schemeDataURI'] = "http://www.archivesdefrance.culture.gouv.fr/gerer/classement/normesoutils/thesaurus/";	
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordReference['schemeVersionID'] = "version 2009";			
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordType = "subject";
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordType["listVersionID"] = "edition 2009";	
		}
		
		$archiveTransfer->Contains->Appraisal->Code = "conserver";
		$archiveTransfer->Contains->Appraisal->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Appraisal->Duration = "P1Y";
		$archiveTransfer->Contains->Appraisal->StartDate = date('Y-m-d',strtotime($this->latestDate));

		$archiveTransfer->Contains->AccessRestriction->Code = $this->getAccessRestriction($transactionsInfo['classification'],$transactionsInfo['nature_code']);
		$archiveTransfer->Contains->AccessRestriction->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->AccessRestriction->StartDate = date('Y-m-d',strtotime($this->latestDate));
		
		
		$archiveTransfer->Contains->Contains[0] = $this->getDL("Contains","Acte soumis au contr�le de l�galit�", $transactionsInfo['numero_acte_prefecture']);
		
		$archiveTransfer->Contains->Contains[0]->Contains[0]->DescriptionLevel="item";
		$archiveTransfer->Contains->Contains[0]->Contains[0]->DescriptionLevel['listVersionID']="edition 2009";
		$archiveTransfer->Contains->Contains[0]->Contains[0]->Name="Acte";
		
		//TODO pr�voir la signature
		//TODO pr�voir les actes en XML
		$archiveTransfer->Contains->Contains[0]->Contains[0]->Document = $this->getDocument($transactionsInfo['actes_file'], "application/pdf",false,"Acte", $this->actesIsSigned);
		
		if ($this->annexe) {
			$c = $this->getDL("Contains","Annexe(s) d'un acte soumis au contr�le de l�galit�");
			foreach($this->annexe as $i => $annexe){
				$c->Document[$i] = $this->getDocument($annexe[0],$annexe[1],false,"Annexe n� ".($i+1),$annexe[2]);
			}
			$archiveTransfer->Contains->Contains[0]->Contains[] =  $c;
		}
		$c = $this->getDL("Contains","Accus� de r�ception d'un acte soumis au contr�le de l�galit�",$transactionsInfo['numero_acte_prefecture']);
		$c->Document = $this->getDocument($this->arActes, "application/xml",$this->actesTransactionsStatusInfo['date'],false,true);
		$archiveTransfer->Contains->Contains[0]->Contains[] = $c;
		
                if (!empty($this->relatedTransaction)){
                    foreach($this->relatedTransaction as $i => $relatedTransactionInfo){
			//print_r($relatedTransactionInfo);
			if ($relatedTransactionInfo['related_transaction_id'] != $transactionsInfo['id']){
				continue;
			}
			
			$archiveTransfer->Contains->Contains[$i+1] =$this->getDL("Contains",$this->getRelatedTransactionName($relatedTransactionInfo['type']), $transactionsInfo['numero_acte_prefecture']);
			$archiveTransfer->Contains->Contains[$i+1]->Contains[0]->DescriptionLevel="item";
			$archiveTransfer->Contains->Contains[$i+1]->Contains[0]->DescriptionLevel['listVersionID']="edition 2009";
			$archiveTransfer->Contains->Contains[$i+1]->Contains[0]->Name= $this->getRelatedTransactionType($relatedTransactionInfo['type']);
			$archiveTransfer->Contains->Contains[$i+1]->Contains[0]->Document 
				= $this->getDocument($relatedTransactionInfo['file_name'],"application/pdf",false,$this->getRelatedTransactionType($relatedTransactionInfo['type']),false,$transactionsInfo['decision_date']);

			$nb_contains_contains  = 1 ;
			if(! empty($relatedTransactionInfo['status_info']['flux_retour'])){
				$archiveTransfer->Contains->Contains[$i+1]->Contains[$nb_contains_contains] 
					= $this->getDL("Contains",$this->getARName($relatedTransactionInfo['type']));
				$archiveTransfer->Contains->Contains[$i+1]->Contains[$nb_contains_contains]->Document 
					= $this->getDocument("AR1-{$relatedTransactionInfo['id']}.xml","application/xml",false,"Accus� de r�ception",false,false,false);
				$nb_contains_contains  = 2 ;
			}
			
				
			foreach($this->relatedTransaction as $reponseTransaction){
				if ($reponseTransaction['related_transaction_id'] != $relatedTransactionInfo['id']){
					continue;
				}
				$archiveTransfer->Contains->Contains[$i+1]->Contains[$nb_contains_contains] 
					= $this->getDL("Contains",$this->getReponseName($reponseTransaction['type']));
				foreach($reponseTransaction['many_file_name'] as $file_nb => $file_name){
					$archiveTransfer->Contains->Contains[$i+1]->Contains[$nb_contains_contains]->Document[$file_nb] 
						= $this->getDocument($file_name,$reponseTransaction['many_file_type'][$file_nb],false,$this->getReponseDocumentName($reponseTransaction['type']),false,false,$reponseTransaction['decision_date']);
				}
				$nb_contains_contains++;
				if (! empty($reponseTransaction['status_info_recu']['flux_retour'])){
					$archiveTransfer->Contains->Contains[$i+1]->Contains[$nb_contains_contains] 
						= $this->getDL("Contains",$this->getARRecuType($relatedTransactionInfo['type']));
					$archiveTransfer->Contains->Contains[$i+1]->Contains[$nb_contains_contains]->Document 
						= $this->getDocument("AR2-{$reponseTransaction['id']}.xml","application/xml",false,"Accus� de r�ception",false,$reponseTransaction['status_info_recu']['date'],false);
		
					$nb_contains_contains++;
				}
			}//fin foreach $this->relatedTransaction as $responseTransaction
                    }//fin foreach qui parcours le tableau $this->relatedTransaction
                }//fin if verifie si le tableau $this->relatedTransaction est vide pour eviter un warning dans les logs
		
		$xml_string =  $archiveTransfer->asXML();
		$xml_string = str_replace("####SAE_ID_VERSANT####", $this->authorityInfo['identifiant_versant'], $xml_string);
		$xml_string = str_replace("####SAE_ID_ARCHIVE####", $this->authorityInfo['identifiant_archive'], $xml_string);
		$xml_string = str_replace("####SAE_ORIGINATING_AGENCY####", $this->authorityInfo['originating_agency'], $xml_string);
                $xml_string = str_replace("&#039;", "'", $xml_string);
		return $xml_string;
	}
	
	public function getARRecuType($type){
		$array = array(	
				3=>"Accus� de r�ception d'une r�ponse � une demande de pi�ces compl�mentaires",
				4=>"Accus� de r�ception d'une r�ponse � une lettre d'observations",
		);
		return $array[$type];
	}
		

	
	public function getARName($type){
		$array = array(	
				3=>"Accus� de r�ception d'une demande de pi�ces compl�mentaires",
				4=>"Accus� de r�ception d'une lettre d'observations",
		);
		return $array[$type];
		
	}
	
	public function getReponseDocumentName($type){
			$array = array(	
						2=>"R�ponse � un courrier simple",
						3=>"R�ponse",
						4=>"R�ponse",
						);
		return $array[$type];
	}
	
	public function getReponseName($type){
		$array = array(	
						2=>"R�ponse � un courrier simple",
						3=>"R�ponse � une demande de pi�ces compl�mentaires",
						4=>"R�ponse � une lettre d'observations",
						);
		return $array[$type];
	}
	
	public function getRelatedTransactionName($type){
		$array = array(	
						2=>"Envoi d'un courrier simple",
						3=>"Envoi d'une demande de pi�ces compl�mentaires",
						4=>"Envoi d'une lettre d'observations",
						5=>"D�f�r� au tribunal administratif");
		return $array[$type];
	}
	
	public function getRelatedTransactionType($type){
		$array = array(	
						2=>"Courrier simple",
						3=>"Demande de pi�ces compl�mentaires",
						4=>"Lettre d'observations",
						5=>"D�f�r� au tribunal administratif");
		return $array[$type];
	}
	
	
	public function getDL($node_name,$name,$id = false){
		$node = new ZenXML($node_name);
		$node->DescriptionLevel = "file"; 
		$node->DescriptionLevel['listVersionID'] = "edition 2009";
		$node->Name =$name;
		if ($id !== false ){
			$node->TransferringAgencyObjectIdentifier = $id;
			$node->TransferringAgencyObjectIdentifier['schemeAgencyName'] = "Minist�re de l'int�rieur, de l'outre-mer et des collectivit�s territoriales";
		}
		return $node;
	}
	
	private function getDocument($filename,$mimetype,$receipt = false,$description = false,$is_original = true,$receipt_submission=false,$response=false){
		$document = new ZenXML("Document");
		$document->Attachment['mimeCode'] = $mimetype;
		$document->Attachment['filename'] = $filename;
		$document->Control = "false";
		$document->Copy = $is_original?"false":"true";
		if ($description !== false){
			$document->Description = $description;
		}		
		if ($receipt){
			$document->Receipt = date("c",strtotime($receipt));
		}
		if ($receipt_submission){
			$document->Receipt = date("c",strtotime($receipt_submission));
		}
		if ($response){
			$document->Response = date("c",strtotime($response));
		}
		$document->Type = "CDO";
		$document->Type["listVersionID"] = "edition 2009";
		
		return $document;
	}
	
	public function getContainsElement($description){
		$contains = new ZenXML("Contains");		
		$contains->DescriptionLevel = "file";
		$contains->DescriptionLevel['listVersionID'] = "edition 2009";
		$contains->Name = $description;
		return $contains;
	}
	
	private function getSujetActes($classification){

		$info = array(	"1" => "Commande publique" ,
						"2" => "Urbanisme", 
						"3" => "Domaine et patrimoine",
						"3.4" => "Circonscription territoriale",
						"4" => "Personnel",
						"5" => "Election politique, Collectivit� locale",
						"5.2" => "Organe d�lib�rant",
						"5.6" => "Elu",
						"5.7" => "Etablissement public de coop�ration intercommunale",
						"5.8" => "Justice",
						"6" => "Police, Protection civile",
						"7" => "Finances locales",
						"7.1" => "Comptabilit� publique",
						"7.2" => "Fiscalit�",
						"7.3" => "Dette publique",
						"7.6" => "Comptabilit� publique",
						"8" => "Education",
						"8.2" => "Protection sociale",
						"8.3" => "R�seau routier",
						"8.4" => "Am�nagement du territoire",
						"8.5" => "Politique de la ville, Immobilier",
						"8.6" => "Emploi",
						"8.7" => "Transport",
						"8.8" => "Environnement",
						"8.9" => "Culture",);
		$debut = substr($classification,0,3);
		
		if (isset($info[$debut])){
			return $info[$debut];
		}
		
		$debut = substr($classification,0,1);
		if (isset($info[$debut])){
			return $info[$debut];
		}
		
		throw new Exception("La classification de cet actes est inconnu");
		
	}
	
	public function getAccessRestriction($classification,$nature){
		if ($classification[0] == 4 && in_array($nature,array(3,4))){
			return "AR048";
		}
                if($classification[0] == 8 && $classification[1] == 2 && $nature == 3){
			return "AR048";
		}
		return "AR038";
	}
	
	public function getDuration($nature){
		switch($nature){
			case 4 :
				return "P10Y";
			case 2:
			case 3:	
			case 5: 
				return "P5Y";
			case 1:  
			default: 
				return "P1Y";
		}
	}
}