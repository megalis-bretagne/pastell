<?php
class JobManager {

	private $jobQueueSQL;
	private $document;
	private $documentActionEntite;
	private $documentTypeFactory;
	private $fluxEntiteSQL;
	private $entiteSQL;
	private $connecteurEntiteSQL;
	private $workerSQL;

	public function __construct(
		JobQueueSQL $jobQueueSQL,
		Document $document,
		DocumentActionEntite $documentActionEntite,
		DocumentTypeFactory $documentTypeFactory,
		FluxEntiteSQL $fluxEntiteSQL,
		EntiteSQL $entiteSQL,
		ConnecteurEntiteSQL $connecteurEntiteSQL,
		WorkerSQL $workerSQL
	){
		$this->jobQueueSQL = $jobQueueSQL;
		$this->document = $document;
		$this->documentActionEntite = $documentActionEntite;
		$this->documentTypeFactory = $documentTypeFactory;
		$this->fluxEntiteSQL = $fluxEntiteSQL;
		$this->entiteSQL = $entiteSQL;
		$this->connecteurEntiteSQL = $connecteurEntiteSQL;
		$this->workerSQL = $workerSQL;
	}

	public function setJobForDocument($id_e,$id_d,$last_message){
		$job = $this->getJob($id_e,$id_d,0,'',$last_message);
		$this->addJobForDocument($job);
	}

	public function setTraitementLot($id_e,$id_d,$id_u,$action){
		$job = $this->getJob($id_e,$id_d,$id_u,$action,"Action programmée sur le document");
		$this->addJobForDocument($job);
	}

	public function setJobForConnecteur($id_ce,$last_message){

		$info = $this->connecteurEntiteSQL->getInfo($id_ce);
		
		$job = new Job();
		$job->type = Job::TYPE_CONNECTEUR;
		$job->id_e = $info['id_e'];
		$job->id_ce = $info['id_ce'];
		$job->last_message = $last_message;
		$job->next_try_in_minutes = $info['frequence_en_minute']?:1;
		$job->id_verrou = $info['id_verrou'];

		/** @var DocumentType $documentType */
		$documentType = null;
		if ($info['id_e']){
			$documentType = $this->documentTypeFactory->getEntiteDocumentType($info['id_connecteur']);
		} else {
			$documentType = $this->documentTypeFactory->getGlobalDocumentType($info['id_connecteur']);
		}

		$all_action = $documentType->getAction()->getAutoAction();
		foreach($all_action as $action){
			$job->etat_source = $action;
			$job->etat_cible = $action;
			$this->addJobForConnecteur($job);
		}
	}


	private function addJobForConnecteur(Job $job){
		if (DISABLE_JOB_QUEUE) {
			return true;
		}
		$job_info = $this->jobQueueSQL->getInfoFromConnecteur($job);
		if (! $job_info){
			return $this->jobQueueSQL->createJob($job);
		}
		return $this->jobQueueSQL->updateSameJob($job,$job_info);
	}


	private function addJobForDocument(Job $job){

		if (DISABLE_JOB_QUEUE) {
			return true;
		}

		if (! $job->etat_cible){
			$this->jobQueueSQL->deleteJob($job);
			return 0;
		}

		$job_info = $this->jobQueueSQL->getInfoFromDocument($job);
		if (! $job_info){
			return $this->jobQueueSQL->createJob($job);
		}


		if ($job_info['etat_cible'] != $job->etat_cible){
			$this->jobQueueSQL->deleteJob($job);
			return $this->jobQueueSQL->createJob($job);
		}

		return $this->jobQueueSQL->updateSameJob($job,$job_info);
	}


	private function getJob($id_e,$id_d,$id_u,$action='',$last_message){
		$infoDocument = $this->document->getInfo($id_d);

		$job = new Job();
		$job->type = Job::TYPE_DOCUMENT;
		$job->id_e = $id_e;
		$job->id_d = $id_d;
		$job->id_u = $id_u;
		$job->last_message = $last_message;

		$job->etat_source = $this->documentActionEntite->getLastAction($id_e, $id_d);
		if ($action) {
			$job->etat_cible = $action;
		} else {
			$job->etat_cible = $this->documentTypeFactory->getFluxDocumentType($infoDocument['type'])->getAction()->getActionAutomatique($job->etat_source);
		}

		$connecteur_type = $this->documentTypeFactory->getFluxDocumentType($infoDocument['type'])->getAction()->getProperties($job->etat_cible,'connecteur-type');

		if ($connecteur_type){
			$fluxEntiteSQL = $this->fluxEntiteSQL;
			$connecteur_info = $fluxEntiteSQL->getConnecteur($id_e,$infoDocument['type'],$connecteur_type);
			if ($connecteur_info){
				$job->next_try_in_minutes = $connecteur_info['frequence_en_minute']?:1;
				$job->id_verrou = $connecteur_info['id_verrou'];
			}
		}
		return $job;
	}
	
	public function deleteConnecteur($id_ce){
		$this->jobQueueSQL->deleteConnecteur($id_ce);
	}

	public function hasActionProgramme($id_e,$id_d){
		return $this->jobQueueSQL->hasDocumentJob($id_e,$id_d);
	}

	public function getActionEnCours($id_e,$id_d){
		return $this->workerSQL->getActionEnCours($id_e, $id_d);
	}

}