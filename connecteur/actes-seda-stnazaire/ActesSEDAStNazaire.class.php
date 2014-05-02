<?php 

class ActesSEDAStNazaire extends SEDAConnecteur {
	
	private $authorityInfo;
	private $seda_config;
		
	public function  setConnecteurConfig(DonneesFormulaire $seda_config){
		$this->authorityInfo = array(
				"sae_id_versant" =>  $seda_config->get("identifiant_versant"),
				"sae_id_archive" =>  $seda_config->get("identifiant_archive"),
				"sae_numero_aggrement" =>  $seda_config->get("numero_agrement"),
				"sae_originating_agency" =>  $seda_config->get("originating_agency"),
				"nom_entite" =>   $seda_config->get('nom_entite'),
				"siren_entite" =>  $seda_config->get('siren_entite'),
		);

		$this->seda_config = $seda_config;
	}

	private function getTransferIdentifier(){
                $last_date = $this->seda_config->get("date_dernier_transfert");
                $numero_transfert = $this->seda_config->get("dernier_numero_transfert");

                $date = date('Y-m-d');
                if ($last_date == $date){
                        $numero_transfert ++;
                } else {
                        $numero_transfert = 1;
                }

                $this->seda_config->setData('date_dernier_transfert', $date);
                $this->seda_config->setData('dernier_numero_transfert', $numero_transfert);

                return "AA_".$this->authorityInfo['siren_entite']."_001-". $date ."-".$numero_transfert;
        }

	
	private function checkInformation(array $information){
		$info = array('numero_acte_collectivite','subject','decision_date',
					'nature_descr','nature_code','classification',
					'latest_date','actes_file','ar_actes',
		);		
		foreach($info as $key){
			if (empty($information[$key])){
				throw new Exception("Impossible de g�n�rer le bordereau : le param�tre $key est manquant. ");
			}
		}
		
		$info = array('annexe','echange_prefecture','echange_prefecture_ar','echange_prefecture_type');
		foreach($info as $key){
			if (! isset($information[$key])){
				throw new Exception("Impossible de g�n�rer le bordereau : le param�tre $key est manquant. ");
			}
		}
		
		$info_sup = array('actes_file_orginal_filename','annexe_original_filename','echange_prefecture_original_filename');
		
		foreach($info_sup as $key){
			if (empty($information[$key])){
				$information[$key] = false;
			}
		}	
		
		return $information;
	}

	public function getBordereau(array $transactionsInfo){
		$transactionsInfo = $this->checkInformation($transactionsInfo);
		$transactionsInfo['classification_tab'] = explode('.',$transactionsInfo['classification']);
		$archiveTransfer = new ZenXML('ArchiveTransfer');
		$archiveTransfer['xmlns'] = "fr:gouv:ae:archive:draft:standard_echange_v0.2";
		$archiveTransfer->Comment = "Transfert d'une d�lib�ration soumise au contr�le de l�galit�";
		$archiveTransfer->Date = date('c');//"2011-08-12T11:03:32+02:00";
		$archiveTransfer->TransferIdentifier = $this->getTransferIdentifier();
		$archiveTransfer->TransferIdentifier['schemeName'] = "S2LOW-ADULLACT";
		
		$archiveTransfer->TransferringAgency->Identification = "####SAE_ID_VERSANT####";
		$archiveTransfer->TransferringAgency->Name = "Tiers de t�l�transmission de l'ADULLACT";

		$archiveTransfer->ArchivalAgency->Identification = "####SAE_ID_ARCHIVE####";
		
		$i = 0;
		foreach(array('ar_actes','actes_file') as $key){
			$fileName = $transactionsInfo[$key];
			$archiveTransfer->Integrity[$i]->Contains = sha1_file($fileName);
			$archiveTransfer->Integrity[$i]->UnitIdentifier = basename($fileName);
			$i++;
		}
		foreach($transactionsInfo['annexe'] as $fileName){
			$archiveTransfer->Integrity[$i]->Contains = sha1_file($fileName);
			$archiveTransfer->Integrity[$i]->UnitIdentifier = basename($fileName);
			$i++;
		}
		
		foreach($transactionsInfo['echange_prefecture'] as $echange_prefecture){
			$archiveTransfer->Integrity[$i]->Contains = sha1_file($echange_prefecture);
			$archiveTransfer->Integrity[$i]->UnitIdentifier = basename($echange_prefecture);
			$i++;
		}
		
		foreach($transactionsInfo['echange_prefecture_ar'] as $echange_prefecture_ar){
			if (! $echange_prefecture_ar){
				continue;
			}
			if (basename($echange_prefecture_ar) == 'empty'){
				continue;
			}
			$archiveTransfer->Integrity[$i]->Contains = sha1_file($echange_prefecture_ar);
			$archiveTransfer->Integrity[$i]->UnitIdentifier = basename($echange_prefecture_ar);
			$i++;
		}
		
		$archiveTransfer->Contains->ArchivalAgreement = $this->authorityInfo['sae_numero_aggrement'];
		$archiveTransfer->Contains->ArchivalAgreement['schemeName'] = "Convention de transfert";
		$archiveTransfer->Contains->ArchivalAgreement['schemeAgencyName'] = "Commune de Saint-Nazaire";
		
		$archiveTransfer->Contains->ArchivalProfile = "DELIBERATIONS";
		$archiveTransfer->Contains->ArchivalProfile['schemeName'] = "Profil de donn�es";
		$archiveTransfer->Contains->ArchivalProfile['listVersionID'] = "2014_03";
		
		$archiveTransfer->Contains->DescriptionLanguage = "fr";
		$archiveTransfer->Contains->DescriptionLanguage['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->DescriptionLevel = "file";
		$archiveTransfer->Contains->DescriptionLevel['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Name = "D�lib�ration de ".$transactionsInfo['nom_entite'].", en date du ".date('d/m/Y',strtotime($transactionsInfo['decision_date'])).", t�l�transmise  la Pr�fecture le ". date('d/m/Y',strtotime($ar_actes_info['DateReception']))." : ".$transactionsInfo['numero_acte_collectivite']." - ".$transactionsInfo['subject'];
		
		$archiveTransfer->Contains->ContentDescription->CustodialHistory = "D�ib�ration sign�e �lectroniquement, soumise au contr�le de l�galit� t�l�transmise via la platei-forme S2LOW de l'ADULLACT pour ". $this->authorityInfo['nom_entite'].". Les donn�es archiv�es sont structur�es selon le sch�ma m�tier ACTES (Aide au Contr�le et � la Transmission �lectronique S�curis�e) �tabli par le Minist�re de l'int�rieur, de l'outre mer et des collectivit�s territoriales en 2005. La description a �t� �tablie selon les r�gles du standard d'�change de donn�es pour l'archivage version 0.2";
			
		$archiveTransfer->Contains->ContentDescription->Description = $transactionsInfo['subject'];
		
		$archiveTransfer->Contains->ContentDescription->Language = "fr";
		$archiveTransfer->Contains->ContentDescription->Language['listVersionID'] = "edition 2009";
		
		$archiveTransfer->Contains->ContentDescription->LatestDate = date('Y-m-d',strtotime($transactionsInfo['latest_date']));
		$archiveTransfer->Contains->ContentDescription->OldestDate = date('Y-m-d',strtotime($transactionsInfo['decision_date']));
		
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Identification = "####SAE_ORIGINATING_AGENCY####";
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Name = "Assembl�es";
	
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordContent = $this->authorityInfo['nom_entite'];
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordReference = $this->authorityInfo['siren_entite'];
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordReference['schemeName'] = "SIRENE";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordReference['schemeAgencyName'] = "INSEE";	
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType = "corpname";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType["listVersionID"] = "edition 2009";
		
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordContent = "Contr�le de l�galit�";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordReference['schemeName'] = "Th�saurus pour la description et l'indexation des archives locales anciennes, modernes et contemporaines_liste d'autorit� Actions";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordReference['schemeAgencyName'] = "Direction des archives de france";	
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordReference['schemeDataURI'] = "http://www.archivesdefrance.culture.gouv.fr/gerer/classement/normesoutils/thesaurus/";	
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordReference['schemeVersionID'] = "version 2009";			
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordType = "subject";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[1]->KeywordType["listVersionID"] = "edition 2009";
		
		
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordContent = "D�lib�ration";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordReference = $transactionsInfo['nature_code'];
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordReference['schemeName'] = "ACTES.codeNatureActe";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordReference['schemeAgencyName'] = "Minist�re de l'int�rieur, de l'outre mer et des collectivit�s territoriales";	
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordReference['schemeVersionID'] = "ACTES V1.4";			
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordType = "genreform";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[2]->KeywordType["listVersionID"] = "edition 2009";

		if ($transactionsInfo['classification_tab'][0] != 9 ){
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordContent = $this->getSujetActes($transactionsInfo['classification']);
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordReference['schemeName'] = "Th�saurus pour la description et l'indexation des archives locales anciennes, modernes et contemporaines_liste d'autorit� Actions";
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordReference['schemeAgencyName'] = "Direction des archives de france";	
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordReference['schemeDataURI'] = "http://www.archivesdefrance.culture.gouv.fr/gerer/classement/normesoutils/thesaurus/";	
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordReference['schemeVersionID'] = "version 2009";			
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordType = "subject";
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordType["listVersionID"] = "edition 2009";	
		}
		
		$archiveTransfer->Contains->Appraisal->Code = "Conserver";
		$archiveTransfer->Contains->Appraisal->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Appraisal->Duration = "P1Y";
		$archiveTransfer->Contains->Appraisal->StartDate = date('Y-m-d',strtotime($transactionsInfo['latest_date']));
	
		
		//$archiveTransfer->Contains->AccessRestriction->Code = $this->getAccessRestriction($transactionsInfo['classification_tab'],$transactionsInfo['nature_code']);
		$archiveTransfer->Contains->AccessRestriction->Code = "AR038";
		$archiveTransfer->Contains->AccessRestriction->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->AccessRestriction->StartDate = date('Y-m-d',strtotime($transactionsInfo['latest_date']));
		
			
		$archiveTransfer->Contains->Contains[0] = $this->getContainsElement("D�lib�ration sign�e �lectroniquement");
		$archiveTransfer->Contains->Contains[0] = $this->getContainsElementWithDocument("D�lib�ration",array($transactionsInfo['actes_file']),false,array($transactionsInfo['actes_file_orginal_filename']));
		
		if($transactionsInfo['annexe']){
			$archiveTransfer->Contains->Contains[] = $this->getContainsElementWithDocument("Annexe(s) de la d�lib�ration sign�e �lectroniquement.",$transactionsInfo['annexe'],false,$transactionsInfo['annexe_original_filename']);
		}

		$arActes = $this->getContainsElementWithDocument("Accus� de r�ception de la d�lib�ration sign�e �lectroniquement.",
															array($transactionsInfo['ar_actes']),
															$transactionsInfo['latest_date']
															);
		
		unset($arActes->Document[0]->Attachment['mimeCode']);
		$archiveTransfer->Contains->Contains[] = $arActes;
		
		
		$result =  $archiveTransfer->asXML();
		$result = str_replace("####SAE_ID_VERSANT####", $this->authorityInfo['identifiant_versant'], $result);
		$result = str_replace("####SAE_ID_ARCHIVE####", $this->authorityInfo['identifiant_archive'], $result);
		$result = str_replace("####SAE_ORIGINATING_AGENCY####", $this->authorityInfo['identifiant_producteur'], $result);
		//echo $result;exit;	
		return $result;
	}
	
	private function getContainsElementWithDocument($description,array $allFileInfo,$receiptDate = false,$original_filename = false){
		$contains = new ZenXML("Contains");
		$contains->DescriptionLevel = "item";
		$contains->DescriptionLevel['listVersionID'] = "edition 2009";
		$contains->Name =  $description ;
		foreach($allFileInfo as $i => $fileInfo){
			if (is_array($fileInfo)){
				$fileName = $fileInfo[0];
				$fileType = $fileInfo[1];
			} else  {
				$fileName = $fileInfo;
				$fileType = $this->getContentType($fileInfo);
			}
			$contains->Document[$i]->Attachment['mimeCode'] = $fileType;
			$contains->Document[$i]->Attachment['filename'] = basename($fileName);
			$contains->Document[$i]->Control = "false";
			$contains->Document[$i]->Copy = "true";
			if ($original_filename && isset($original_filename[$i])){
				$contains->Document[$i]->Description = $original_filename[$i];
			}
			if ($receiptDate) {
				$contains->Document[$i]->Receipt = date('c',strtotime($receiptDate));
			}
			$contains->Document[$i]->Type = "CDO";
			$contains->Document[$i]->Type["listVersionID"] = "edition 2009";
		}
		return $contains;
	}
	
	private function getContainsElement($description){
		$contains = new ZenXML("Contains");		
		$contains->DescriptionLevel = "file";
		$contains->DescriptionLevel['listVersionID'] = "edition 2009";
		$contains->Name = $description;
		return $contains;
	}
	
	private function getSujetActes($classification){

		$info = array(	"1" => "Commande publique",
				"2" => "Urbanisme", 
				"3" => "Propri�t� publique",
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
				"8.1" => "Education",
				"8.2" => "Protection sociale",
				"8.3" => "R�seau routier",
				"8.4" => "Amenagement du territoire",
				"8.5" => "Politique de la ville, Immobilier",
				"8.6" => "Emploi",
				"8.7" => "Transport",
				"8.8" => "Environnement",
				"8.9" => "Culture");
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
	
	private function getAccessRestriction($classification,$nature){
		if(!is_array($classification)){
			$classification = explode('.',$classification);
		}
		if ($classification[0] == 4 && in_array($nature,array(3,4))){
			return "AR048";
		}
		return "AR038";
	}
	
	private function getDuration($nature){
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
	private function getDL($node_name,$name,$id = false){
		$node = new ZenXML($node_name);
		$node->DescriptionLevel = "file";
		$node->DescriptionLevel['listVersionID'] = "edition 2009";
		$node->Name =$name;
		if ($id !== false ){
			$node->TransferringAgencyObjectIdentifier = "$id";
			$node->TransferringAgencyObjectIdentifier['schemeAgencyName'] = "Minist�re de l'int�rieur, de l'outre-mer et des collectivit�s territoriales";
		}
		return $node;
	}
	
	
	private function getARRecuType($type){
		$array = array(
				'3R'=>"Accus� de r�ception d'une r�ponse � une demande de pi�ces compl�mentaires",
				'4R'=>"Accus� de r�ception d'une r�ponse � une lettre d'observations",
		);
		if (empty($array[$type])){
			throw new Exception("Accus� de r�ception non autoris� sur ce type de message (message $type)");
		}
		return $array[$type];
	}
	
	
	
	private function getARName($type){
		$array = array(
				'3A'=>"Accus� de r�ception d'une demande de pi�ces compl�mentaires",
				'4A'=>"Accus� de r�ception d'une lettre d'observations",
		);
		if (empty($array[$type])){
			throw new Exception("Accus� de r�ception non autoris� sur ce type de message (message $type)");
		}
		return $array[$type];
	
	}
	
	private function getReponseDocumentName($type){
		$array = array(
				'2R'=>"R�ponse � un courrier simple",
				'3R'=>"R�ponse",
				'4R'=>"R�ponse",
		);
		return $array[$type];
	}
	
	private function getReponseName($type){
		$array = array(
				'2R'=>"R�ponse � un courrier simple",
				'3R'=>"R�ponse � une demande de pi�ces compl�mentaires",
				'4R'=>"R�ponse � une lettre d'observations",
		);
		return $array[$type];
	}
	
	private function getRelatedTransactionName($type){
		$array = array(
				'2A'=>"Envoi d'un courrier simple",
				'3A'=>"Envoi d'une demande de pi�ces compl�mentaires",
				'4A'=>"Envoi d'une lettre d'observations",
				'5A'=>"D�f�r� au tribunal administratif");
		return $array[$type];
	}
	
	private function getRelatedTransactionType($type){
		$array = array(
				'2A'=>"Courrier simple",
				'3A'=>"Demande de pi�ces compl�mentaires",
				'4A'=>"Lettre d'observations",
				'5A'=>"D�f�r� au tribunal administratif");
		return $array[$type];
	}
	private function getContentType($file_path){
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		return finfo_file($finfo,$file_path);
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
}
