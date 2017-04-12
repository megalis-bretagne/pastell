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
		if (DISABLE_JOB_QUEUE) {
			return true;
		}

		$job = $this->getJobForDocument($id_e,$id_d,0,'',$last_message);
		return $this->addJobForDocument($job);
	}

	public function setTraitementLot($id_e,$id_d,$id_u,$action){
		if (DISABLE_JOB_QUEUE) {
			return true;
		}

		$job = $this->getJobForDocument($id_e,$id_d,$id_u,$action,"Action programmée sur le document");
		return $this->addJobForDocument($job);
	}

	public function setJobForConnecteur($id_ce,$action_name,$last_message){
		if (DISABLE_JOB_QUEUE) {
			return true;
		}
		$id_job = $this->jobQueueSQL->getJobIdForConnecteur($id_ce,$action_name);

		if (! $id_job){
			return $this->createJobForConnecteur($id_ce,$action_name);
		}

		$this->updateJobForConnecteur($id_job,$last_message);
		return $id_job;
	}

	private function createJobForConnecteur($id_ce,$action_name){
		$info = $this->connecteurEntiteSQL->getInfo($id_ce);
		$job = new Job();
		$job->type = Job::TYPE_CONNECTEUR;
		$job->id_e = $info['id_e'];
		$job->id_ce = $info['id_ce'];
		$this->setFrequenceInfo($job);
		$job->etat_source = $action_name;
		$job->etat_cible = $action_name;
		return $this->jobQueueSQL->createJob($job);
	}

	private function updateJobForConnecteur($id_job,$last_message){
		$job = $this->jobQueueSQL->getJob($id_job);
		$job->last_message = $last_message;
		$this->setFrequenceInfo($job);
		$now = date('Y-m-d H:i:s');
		$next_try = date('Y-m-d H:i:s',strtotime("+ {$job->next_try_in_minutes} minutes"));

		if ($job->nb_try == 0){
			$job->first_try = $now;
		}

		$job->nb_try++;
		$job->next_try = $next_try;
		$job->last_try = $now;

		$this->jobQueueSQL->updateJob($job);
	}


	private function setFrequenceInfo($job){
		$info = $this->connecteurEntiteSQL->getInfo($job->id_ce);
		$job->next_try_in_minutes = $info['frequence_en_minute']?:1;
		$job->id_verrou = $info['id_verrou'];
		return $job;
	}


	private function addJobForDocument(Job $job){


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


	private function getJobForDocument($id_e, $id_d, $id_u, $action='', $last_message){
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