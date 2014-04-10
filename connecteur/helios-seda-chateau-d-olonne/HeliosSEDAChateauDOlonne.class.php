<?php 
class HeliosSEDAChateauDOlonne extends Connecteur {
	
	private $authorityInfo;
	
	public function  setConnecteurConfig(DonneesFormulaire $seda_config){
		$this->authorityInfo = array(
				"sae_id_versant" =>  $seda_config->get("identifiant_versant"),
				"sae_id_archive" =>  $seda_config->get("identifiant_archive"),
				"sae_numero_aggrement" =>  $seda_config->get("numero_agrement"),
				"sae_originating_agency" =>  $seda_config->get("originating_agency"),
				"name" =>   $seda_config->get('nom'),
				"siren" =>  $seda_config->get('siren'),
				"sae_identifiant_producteur" => $seda_config->get("sae_identifiant_producteur"),
		);
	}
	
	public function checkInformation(array $information){
		$info = array('unique_id','date','description','pes_description','pes_retour_description','pes_aller','pes_retour','size');		
		foreach($info as $key){
			if (empty($information[$key])){
				throw new Exception("Impossible de g�n�rer le bordereau : le param�tre $key est manquant. ");
			}
		}
	}
	
	private function getTransferIdentifier($transactionsInfo) {
		return sha1_file($transactionsInfo['pes_aller']) ."-". time();
	}
	
	public function getBordereau($transactionsInfo){
		$this->checkInformation($transactionsInfo);
		$archiveTransfer = new ZenXML('ArchiveTransfer');
		$archiveTransfer['xmlns'] = "fr:gouv:ae:archive:draft:standard_echange_v0.2";
		$archiveTransfer->Comment = "Transfert d'un flux comptable conforme au PES V2";
		$archiveTransfer->Date = date('c');//"2011-08-12T11:03:32+02:00";
		
		#TODO : Identifiant unique CIVIL Finances - Identifiant unique du transfert g�n�r� par le logiciel CIVIL Finances (CIRIL)
		$archiveTransfer->TransferIdentifier = $this->getTransferIdentifier($transactionsInfo);
		
		$archiveTransfer->TransferringAgency->Identification = $this->authorityInfo['siren'];
		$archiveTransfer->TransferringAgency->Identification['schemeName'] = "SIRENE";
		$archiveTransfer->TransferringAgency->Identification['schemeAgencyName'] = "INSEE";
		$archiveTransfer->TransferringAgency->Name = "Ville de Ch�teau d'Olonne";
		$archiveTransfer->TransferringAgency->Address->BuildingName = "Mairie de Ch�teau d'Olonne";
		$archiveTransfer->TransferringAgency->Address->BuildingNumber = "53";
		$archiveTransfer->TransferringAgency->Address->CityName = "Le Ch�teau d'Olonne";
		$archiveTransfer->TransferringAgency->Address->Postcode = "85180";
		$archiveTransfer->TransferringAgency->Address->StreetName = "Rue S�raphin Buton";
		
		$archiveTransfer->ArchivalAgency->BusinessType = "FRAM85060";
		$archiveTransfer->ArchivalAgency->Identification = "FRAM85060";
		$archiveTransfer->ArchivalAgency->Identification['schemeName'] = "SIRENE";
		$archiveTransfer->ArchivalAgency->Identification['schemeAgencyName'] = "INSEE";
		$archiveTransfer->ArchivalAgency->Name = "Archives municipales du Ch�teau-d'Olonne";
		
		$archiveTransfer->ArchivalAgency->Contact->DepartementName = "Direction des Services � la Population";
		$archiveTransfer->ArchivalAgency->Contact->PersonName = "Archives Municipales du Ch�teau d'Olonne";
		$archiveTransfer->ArchivalAgency->Contact->Responsability = "";
		
		$archiveTransfer->ArchivalAgency->Contact->Address->BuildingName = "Mairie de Ch�teau d'Olonne";
		$archiveTransfer->ArchivalAgency->Contact->Address->BuildingNumber = "53";
		$archiveTransfer->ArchivalAgency->Contact->Address->CityName = "Le Ch�teau d'Olonne";
		$archiveTransfer->ArchivalAgency->Contact->Address->Postcode = "85180";
		$archiveTransfer->ArchivalAgency->Contact->Address->StreetName = "Rue S�raphin Buton";
		
		//TODO : il n'y a pas les integrity dans le document de sp�cification
		$i = 0;
		foreach(array('pes_aller','pes_retour') as $file_to_add){
			$file_path = $transactionsInfo[$file_to_add];
			$archiveTransfer->Integrity[$i]->Contains = sha1_file($file_path);
			$archiveTransfer->Integrity[$i]->UnitIdentifier = basename($file_path);
			$i++;
		}
		
		$archiveTransfer->Contains->ArchivalAgreement = "Convention de transfert d'archives";
		//TODO rien n'est sp�cifier sur o� mettre le num�ro d'aggr�ment, je le mets ici
		$archiveTransfer->Contains->ArchivalAgreement['schemeID'] = $this->authorityInfo['sae_numero_aggrement'];
		$archiveTransfer->Contains->ArchivalAgreement['schemeName'] = "Identification_accords_d'archivage";
		$archiveTransfer->Contains->ArchivalAgreement['schemeAgencyName'] = "Archives_municipales_Chateau_d_Olonne";
		//TODO : aucune information sp�cifier pour le format de la date
		$archiveTransfer->Contains->ArchivalProfile = "Flux comptable PES en date du {$transactionsInfo['date']} : Mairie du Ch�teau-d'Olonne";
		$archiveTransfer->Contains->ArchivalProfile['schemeName'] = "Profil_flux_comptable_PES_V2";
		$archiveTransfer->Contains->ArchivalProfile['schemeAgencyName'] = "Minist�re du bugdet, des comptes publics et de la fonction publique";		
		$archiveTransfer->Contains->DescriptionLanguage = "fr";
		$archiveTransfer->Contains->DescriptionLanguage['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->DescriptionLevel = "file";
		$archiveTransfer->Contains->DescriptionLevel['listVersionID'] = "edition 2009";
		//TODO : la d�finition et le commentaire ne sont pas coh�rent		
		$archiveTransfer->Contains->Name = $this->authorityInfo['siren'];
		
		$archiveTransfer->Contains->ContentDescription->CustodialHistory = "Les pi�ces soumises au contr�le du comptable public sont int�gr�es au flux comptable PES V2 d�fini par le programme Helios et sont transf�r�es pour archivage depuis le tiers de t�l�transmission SLOW ou la passerelle Helios puis depuis Pastel ( Adullact) pour le compte de la ville du Ch�teau-d'Olonne. \"La description a �t� �tablie selon les r�gles du standard d'�change de donn�es pour l'archivage �lectronique version0.2, publi� dans le r�f�rentiel g�n�ral d'interop�rabilit�.";
		$archiveTransfer->Contains->ContentDescription->Language = "fr";
		$archiveTransfer->Contains->ContentDescription->Language['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->ContentDescription->LatestDate = date('Y-m-d',strtotime($transactionsInfo['date']));		
		$archiveTransfer->Contains->ContentDescription->OldestDate = date('Y-m-d',strtotime($transactionsInfo['date']));
		//TODO que est le format ? je donne la taille en octets		
		$archiveTransfer->Contains->ContentDescription->Size = $transactionsInfo['size'];
		$archiveTransfer->Contains->ContentDescription->Size['unitCode'] = "4L";
		
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Identification = $this->authorityInfo['sae_originating_agency'];
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Identification['schemeName'] = "SIREN";
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Identification['schemeAgenctName'] = "INSEE";
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Name="Direction des finances et du contr�le de gestion";
		
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Contact->Identification = $this->authorityInfo['sae_identifiant_producteur'];
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Contact->PersonName = "Direction des finances et du contr�le de gestion de la Ville du Ch�teau d'Olonne";
		
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Address->BuildingName = "Mairie de Ch�teau d'Olonne";
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Address->BuildingNumber = "53";
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Address->CityName = "Le Ch�teau d'Olonne";
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Address->Postcode = "85180";
		$archiveTransfer->Contains->ContentDescription->OriginatingAgency->Address->StreetName = "Rue S�raphin Buton";
		
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordContent = "COMPTABILITE PUBLIQUE";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordReference = "Thesaurus_matiere du Service Interminist�riel des archives de France";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType = "subject";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType["listVersionID"] = "edition 2009";
		
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordContent = "PIECE COMPTABLE";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordReference = "Liste d'autorit�_typologie documentaire du Service Interminist�riel des Archives de France";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType = "subject";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType["listVersionID"] = "edition 2009";
		
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordContent = "LIVRE COMPTABLE";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordReference = "Liste d'autorit� typologie documentaire du Service Interminist�riel de France";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType = "genreform";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType["listVersionID"] = "edition 2009";

		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordContent = "Ville du Chateau d'Olonne ";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordReference = "";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType = "corpname";
		$archiveTransfer->Contains->ContentDescription->ContentDescriptive[0]->KeywordType["listVersionID"] = "edition 2009";
		
		$archiveTransfer->Contains->Appraisal->Code = "detruire";
		$archiveTransfer->Contains->Appraisal->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Appraisal->Duration = "P10Y";
		$archiveTransfer->Contains->Appraisal->StartDate =  date('Y-m-d',strtotime($transactionsInfo['date']));
	
		$archiveTransfer->Contains->Contains[0]->DescriptionLevel = "item";
		$archiveTransfer->Contains->Contains[0]->DescriptionLevel['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Contains[0]->Name = "Journal des transmissions";
		
		$archiveTransfer->Contains->Contains[0]->ContentDescription->Language='fr';
		$archiveTransfer->Contains->Contains[0]->ContentDescription->Language['listVersionID'] = "edition 2009";

		$archiveTransfer->Contains->Contains[0]->AccessRestriction->Code = "AR038";
		$archiveTransfer->Contains->Contains[0]->AccessRestriction->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Contains[0]->AccessRestriction->StartDate = date('Y-m-d',strtotime($transactionsInfo['date']));

		$archiveTransfer->Contains->Contains[0]->Document->Attachment['format'] = 'fmt/101';
		$archiveTransfer->Contains->Contains[0]->Document->Attachment['mimeCode'] = 'text/xml';
		//TODO : c'est quoi le journal des transmissions ? J'ai mis le PES ALLER
		$archiveTransfer->Contains->Contains[0]->Document->Attachment['filename'] = basename($transactionsInfo['pes_aller']);
		$archiveTransfer->Contains->Contains[0]->Document->Description="Journal des transmissions";
		$archiveTransfer->Contains->Contains[0]->Document->Type = "CDO";
		$archiveTransfer->Contains->Contains[0]->Document->Type['listVersionID'] = "edition 2009";

		$archiveTransfer->Contains->Contains[1]->DescriptionLevel = "item";
		$archiveTransfer->Contains->Contains[1]->DescriptionLevel['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Contains[1]->Name = "PES";
		//TODO : ????
		$archiveTransfer->Contains->Contains[1]->ContentDescription->Description='?????';
		$archiveTransfer->Contains->Contains[1]->ContentDescription->Language='fr';
		$archiveTransfer->Contains->Contains[1]->ContentDescription->Language['listVersionID'] = "edition 2009";
		
		$archiveTransfer->Contains->Contains[1]->AccessRestriction->Code = "AR038";
		$archiveTransfer->Contains->Contains[1]->AccessRestriction->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Contains[1]->AccessRestriction->StartDate = date('Y-m-d',strtotime($transactionsInfo['date']));
		
		$archiveTransfer->Contains->Contains[1]->Document->Attachment['format'] = 'fmt/101';
		$archiveTransfer->Contains->Contains[1]->Document->Attachment['mimeCode'] = 'text/xml';
		$archiveTransfer->Contains->Contains[1]->Document->Attachment['filename'] = basename($transactionsInfo['pes_aller']);
		$archiveTransfer->Contains->Contains[1]->Document->Description="PES";
		$archiveTransfer->Contains->Contains[1]->Document->Type = "CDO";
		$archiveTransfer->Contains->Contains[1]->Document->Type['listVersionID'] = "edition 2009";
		
		//TODO AMHA la balise Contains devrait arriv� avant la balise Document
		$archiveTransfer->Contains->Contains[1]->Contains[0]->DescriptionLevel = "item";
		$archiveTransfer->Contains->Contains[1]->Contains[0]->DescriptionLevel['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Contains[1]->Contains[0]->Name = "Bordereau Journal";
		//TODO : Je ne comprends pas ce que je dois faire
		$archiveTransfer->Contains->Contains[1]->Contains[0]->ContentDescription->Description='?????';
		$archiveTransfer->Contains->Contains[1]->Contains[0]->ContentDescription->Language='fr';
		$archiveTransfer->Contains->Contains[1]->Contains[0]->ContentDescription->Language['listVersionID'] = "edition 2009";
		
		$archiveTransfer->Contains->Contains[1]->Contains[0]->AccessRestriction->Code = "AR038";
		$archiveTransfer->Contains->Contains[1]->Contains[0]->AccessRestriction->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Contains[1]->Contains[0]->AccessRestriction->StartDate = date('Y-m-d',strtotime($transactionsInfo['date']));
		
		$archiveTransfer->Contains->Contains[1]->Contains[1]->DescriptionLevel = "item";
		$archiveTransfer->Contains->Contains[1]->Contains[1]->DescriptionLevel['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Contains[1]->Contains[1]->Name = "Pi�ces justificatives";
		//TODO : Je ne comprend pas ce que je dois faire
		$archiveTransfer->Contains->Contains[1]->Contains[1]->ContentDescription->Description='?????';
		$archiveTransfer->Contains->Contains[1]->Contains[1]->ContentDescription->Language='fr';
		$archiveTransfer->Contains->Contains[1]->Contains[1]->ContentDescription->Language['listVersionID'] = "edition 2009";
		
		$archiveTransfer->Contains->Contains[1]->Contains[1]->AccessRestriction->Code = "AR038";
		$archiveTransfer->Contains->Contains[1]->Contains[1]->AccessRestriction->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Contains[1]->Contains[1]->AccessRestriction->StartDate = date('Y-m-d',strtotime($transactionsInfo['date']));
		
		$archiveTransfer->Contains->Contains[2]->DescriptionLevel = "item";
		$archiveTransfer->Contains->Contains[2]->DescriptionLevel['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Contains[2]->Name = "PES ACK/NACK";
		
		$archiveTransfer->Contains->Contains[2]->AccessRestriction->Code = "AR038";
		$archiveTransfer->Contains->Contains[2]->AccessRestriction->Code['listVersionID'] = "edition 2009";
		$archiveTransfer->Contains->Contains[2]->AccessRestriction->StartDate = date('Y-m-d',strtotime($transactionsInfo['date']));
		
		$archiveTransfer->Contains->Contains[2]->Document->Attachment['format'] = 'fmt/101';
		$archiveTransfer->Contains->Contains[2]->Document->Attachment['mimeCode'] = 'text/xml';
		$archiveTransfer->Contains->Contains[2]->Document->Attachment['filename'] = basename($transactionsInfo['pes_retour']);
		$archiveTransfer->Contains->Contains[2]->Document->Description="PES";
		$archiveTransfer->Contains->Contains[2]->Document->Type = "CDO";
		$archiveTransfer->Contains->Contains[2]->Document->Type['listVersionID'] = "edition 2009";

		return $archiveTransfer->asXML();
	}
	
}