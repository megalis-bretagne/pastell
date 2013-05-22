<?php 

class ActesSEDAStandard extends Connecteur {
	
	private $authorityInfo;
		
	public function  setConnecteurConfig(DonneesFormulaire $seda_config){
		$this->authorityInfo = array(
				"sae_id_versant" =>  $seda_config->get("sae_identifiant_versant"),
				"sae_id_archive" =>  $seda_config->get("sae_identifiant_archive"),
				"sae_numero_aggrement" =>  $seda_config->get("sae_numero_agrement"),
				"sae_originating_agency" =>  $seda_config->get("sae_originating_agency"),
				"name" =>   $seda_config->get('denomination'),
				"siren" =>  $seda_config->get('siren'),
		);
	}

	
	public function checkInformation(array $information){
		$info = array('unique_id','subject','decision_date',
					'nature_descr','nature_code','classification',
					'latest_date','actes_file');		
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
		$archiveTransfer->TransferIdentifier = $transactionsInfo['unique_id'];
		$archiveTransfer->TransferIdentifier['schemeName'] = "Codification interne";
		
		$archiveTransfer->TransferringAgency->Identification = $this->authorityInfo['sae_id_versant'];
		$archiveTransfer->ArchivalAgency->Identification = $this->authorityInfo['sae_id_archive'];
		
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
		
		$archiveTransfer->Contains->ArchivalAgreement = $this->authorityInfo['sae_numero_aggrement'];
		$archiveTransfer->Contains->ArchivalAgreement['schemeName'] = "Convention de transfert";
		$archiveTransfer->Contains->ArchivalAgreement['schemeAgencyName'] = $this->authorityInfo['name'];
		
		$archiveTransfer->Contains->ArchivalProfile = "ACTES 1.4";
		$archiveTransfer->Contains->ArchivalProfile['schemeName'] = "Profil de donn�es";
		
		$archiveTransfer->Contains->DescriptionLanguage = "fr";
		$archiveTransfer->Contains->DescriptionLanguage['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->DescriptionLevel = "file";
		$archiveTransfer->Contains->DescriptionLevel['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Name = $transactionsInfo['unique_id'];
		
		$archiveTransfer->Contains->ContentDescription->CustodialHistory = "Actes d�mat�rialis�s soumis au contr�le de l�galit�, les donn�es archiv�es sont structur�es selon le sch�ma Actes (Aide au contr�le de l�galit� d�mat�rialis�) �tabli par le minist�re de l'int�rieur, de l'outre mer et des collectivit�s territoriales. La description a �t� �tablie selon les r�gles du standard d'�change de donn�es pour l'archivage version 0.2";
			
		$archiveTransfer->Contains->ContentDescription->Description = $transactionsInfo['subject'];
		
		$archiveTransfer->Contains->ContentDescription->Language = "fr";
		$archiveTransfer->Contains->ContentDescription->Language['listVersionID'] = "edition 2009";
		
		$archiveTransfer->Contains->ContentDescription->LatestDate = date('Y-m-d',strtotime($transactionsInfo['latest_date'] ." + 2 month"));
		$archiveTransfer->Contains->ContentDescription->OldestDate = date('Y-m-d',strtotime($transactionsInfo['decision_date']));
		
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Identification = $this->authorityInfo['sae_originating_agency'];
	
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordContent = $this->authorityInfo['name'];
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordReference = $this->authorityInfo['siren'];
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
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordReference['schemeAgencyName'] = "Direction des archives de france";	
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordReference['schemeDataURI'] = "http://www.archivesdefrance.culture.gouv.fr/gerer/classement/normesoutils/thesaurus/";	
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordReference['schemeVersionID'] = "version 2009";			
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordType = "subject";
			$archiveTransfer->Contains->ContentDescription->ContentDescriptive[3]->KeywordType["listVersionID"] = "edition 2009";	
		}
		
		$archiveTransfer->Contains->Appraisal->Code = "detruire";
		$archiveTransfer->Contains->Appraisal->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Appraisal->Duration = $this->getDuration($transactionsInfo['nature_code']);
		$archiveTransfer->Contains->Appraisal->StartDate = date('Y-m-d',strtotime($transactionsInfo['latest_date'] ." + 2 month"));
	
		
		$archiveTransfer->Contains->AccessRestriction->Code = $this->getAccessRestriction($transactionsInfo['classification'],$transactionsInfo['nature_code']);
		$archiveTransfer->Contains->AccessRestriction->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->AccessRestriction->StartDate = date('Y-m-d',strtotime($transactionsInfo['latest_date'] ." + 2 month"));
		
			
		$archiveTransfer->Contains->Contains[0] = $this->getContainsElement("Transmission d'un acte soumis au contr�le de l�galit�");
		$archiveTransfer->Contains->Contains[0]->Contains[0] = $this->getContainsElementWithDocument("Actes",array(basename($transactionsInfo['actes_file'])));
		
		if($transactionsInfo['annexe']){
			$archiveTransfer->Contains->Contains[0]->Contains[] = $this->getContainsElementWithDocument("Annexe(s) d'un acte soumis au contr�le de l�galit�",$transactionsInfo['annexe']);
		}

		$arActes = $this->getContainsElementWithDocument("Accus� de r�ception d'un acte soumis au contr�le de l�galit�",
															array(basename($transactionsInfo['ar_actes'])),
															$transactionsInfo['latest_date']
															);
		
		unset($arActes->Document[0]->Attachment['mimeCode']);
		$archiveTransfer->Contains->Contains[0]->Contains[] = $arActes;
		
		return $archiveTransfer->asXML();
	}
	
	private function getContainsElementWithDocument($description,array $allFileInfo,$receiptDate = false){
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
				$fileType = "application/pdf";
			}
			$contains->Document[$i]->Attachment['mimeCode'] = $fileType;
			$contains->Document[$i]->Attachment['filename'] = $fileName;
			$contains->Document[$i]->Control = "false";
			$contains->Document[$i]->Copy = "true";
			$contains->Document[$i]->Description = "Acte";
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
	
	private function getAccessRestriction($classification,$nature){
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
	
}