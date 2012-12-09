<?php

class EnvoieCDG  extends ActionExecutor {

	private $collectivite;
	private $cdgProperties;
	private $id_cdg;

	private function getId_cdg(){
		if ( ! $this->id_cdg){
			$infoEntite = $this->getEntite()->getInfo();
			$this->id_cdg = $this->getEntite()->getCDG();	
		}
		return $this->id_cdg;
	}
	
	public function setCDGProperties(EntitePropertiesSQL $cdgProperties) {
		$this->cdgProperties = $cdgProperties;	
	}
	
	public function getCDGProperties(){
		if ( ! $this->cdgProperties){
			$this->setCDGProperties(new EntitePropertiesSQL($this->getSQLQuery()));
		}
		return $this->cdgProperties;
	}
	
	public function go(){
		$actionCreator = $this->getActionCreator();
		
		$id_cdg = $this->getId_cdg();
		if (! $id_cdg){
			$this->setLastMessage("La collectivit� n'a pas de centre de gestion");
			$actionCreator->addAction($this->id_e,$this->id_u,'termine',"Traitement termin�");						
			return false;
		}
		
		if (! $this->getDonneesFormulaire()->get('envoi_cdg')) {
			$this->setLastMessage("Ce document ne doit pas �tre envoy� au centre de gestion");
			$actionCreator->addAction($this->id_e,$this->id_u,'termine',"Traitement termin�");									
			return false;
		}
		
		
		$this->getDocumentEntite()->addRole($this->id_d,$id_cdg,"lecteur");
		
		
		$actionCreator->addAction($this->id_e,$this->id_u,'send-cdg',"Le document a �t� envoy� au centre de gestion");
		$actionCreator->addToEntite($id_cdg,"Le document a �t� envoy� par la collectivit�");
		
		$actionCreator->addAction($id_cdg,0,'recu-cdg',"Le document a �t� re�u par le centre de gestion");
		$actionCreator->addToEntite($this->id_e,"Le document a �t� re�u par le centre de gestion");
		
		
		$infoDocument = $this->getDocument()->getInfo($this->id_d);

		$documentType = $this->getDocumentTypeFactory()->getDocumentType($infoDocument['type']);
				
		$theAction = $documentType->getAction();
		
		
		$message =  "La transaction � ".$infoDocument['titre']." � est pass� dans l'�tat :  " . $theAction->getActionName('send-cdg');
		
		$link = SITE_BASE . "document/detail.php?id_d={$this->id_d}&id_e=$id_cdg";
		
		$message .= "\n\nVous pouvez le consulter ici : $link";
		
		$message .= "\n\n";
		
		$notificationMail = $this->getNotificationMail();
		
		$notificationMail->notify($id_cdg,$this->id_d,'recu-cdg', 'actes',$message);		
		
		$entiteProperties = new EntitePropertiesSQL($this->getSQLQuery());
		
		$has_ged = $entiteProperties->getProperties($id_cdg,EntitePropertiesSQL::ALL_FLUX,'has_ged');
		if ($has_ged == 'auto'){	
			$actionCreator->addAction($id_cdg,0,'send-ged',"Le document a �t� d�pos� dans la GED");
		}
		
		$has_archivage = $entiteProperties->getProperties($id_cdg,EntitePropertiesSQL::ALL_FLUX,'has_archivage');
		if ($has_archivage == 'auto'){	
			$actionCreator->addAction($id_cdg,0,'send-archive',"Le document a �t� archiv�");
		}
		
		
		$this->setLastMessage("Le document a �t� envoy� � votre centre de gestion");
		return true;
	}
}