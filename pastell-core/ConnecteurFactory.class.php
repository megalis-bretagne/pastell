<?php
class ConnecteurFactory {
	
	private $objectInstancier;
	
	public function __construct(ObjectInstancier $objectInstancier){
		$this->objectInstancier = $objectInstancier;
	}
	
	/**
	 * 
	 * @param int $id_ce
	 * @return Connecteur
	 */
	public function getConnecteurById($id_ce){
		$connecteur_info = $this->objectInstancier->ConnecteurEntiteSQL->getInfo($id_ce);
		return $this->getConnecteurObjet($connecteur_info);
	}
	
	/**
	 * 
	 * @param int $id_ce
	 * @return DonneesFormulaire
	 */
	public function getConnecteurConfig($id_ce){
		return $this->objectInstancier->DonneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
	}
	
	public function getConnecteurId($id_e,$id_flux,$type_connecteur){
		$id_ce = $this->objectInstancier->FluxEntiteHeritageSQL->getConnecteurId($id_e,$id_flux,$type_connecteur);
		return $id_ce;
	}
	
	public function getConnecteurByType($id_e,$id_flux,$type_connecteur){
		$id_ce = $this->getConnecteurId($id_e, $id_flux, $type_connecteur);
		if (! $id_ce){
			return false;
		}
		return $this->getConnecteurById($id_ce);
	}
	
	public function getConnecteurConfigByType($id_e,$id_flux,$type_connecteur){
		$id_ce = $this->getConnecteurId($id_e, $id_flux, $type_connecteur);
		if (! $id_ce){
			return false;
		}
		return $this->getConnecteurConfig($id_ce);
	}

	/**
	 * @param $connecteur_info
	 * @return bool|Connecteur
	 * @throws Exception
	 */
	private function getConnecteurObjet($connecteur_info){
		if (!$connecteur_info){
			return false;
		}
		$class_name = $this->objectInstancier->ConnecteurDefinitionFiles->getConnecteurClass($connecteur_info['id_connecteur']);
		/** @var Connecteur $connecteurObject */
		$connecteurObject = $this->objectInstancier->newInstance($class_name);
		$connecteurObject->setConnecteurInfo($connecteur_info);
		$connecteurObject->setLogger($this->objectInstancier->getInstance('Monolog\Logger'));
		$connecteurObject->setConnecteurConfig($this->getConnecteurConfig($connecteur_info['id_ce']));
		return $connecteurObject;
	}
	
	public function getGlobalConnecteur($type){
		return $this->getConnecteurByType(0,'global',$type);
	}
	
	public function getGlobalConnecteurConfig($type){
		return $this->getConnecteurConfigByType(0,'global',$type);
	}
	
	public function getManquant(){
		$result = array(); 
		$all_connecteur_extension = $this->objectInstancier->Extensions->getAllConnecteur();
		$all_connecteur_used = $this->objectInstancier->ConnecteurEntiteSQL->getAllUsed();
		foreach($all_connecteur_used as $connecteur_id){
			if (empty($all_connecteur_extension[$connecteur_id])){
				$result[] = $connecteur_id;
			}
		}
		return $result;
	}
		
	
}