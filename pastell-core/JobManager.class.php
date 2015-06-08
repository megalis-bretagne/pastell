<?php
class JobManager {
	
	private $objectInstancier;
	
	public function __construct(ObjectInstancier $objectInstancier){
		$this->objectInstancier = $objectInstancier;
	}
	
	public function setJobForDocument($id_e,$id_d,$last_message){
		$infoDocument = $this->objectInstancier->Document->getInfo($id_d);
		
		$job = new Job();
		$job->type = Job::TYPE_DOCUMENT;
		$job->id_e = $id_e;
		$job->id_d = $id_d;
		$job->last_message = $last_message;
		
		$job->etat_source = $this->objectInstancier->DocumentActionEntite->getLastAction($id_e, $id_d);
		$job->etat_cible = $this->objectInstancier->DocumentTypeFactory->getFluxDocumentType($infoDocument['type'])->getAction()->getActionAutomatique($job->etat_source);
		
		$this->getJobQueueSQL()->addJob($job);
	}
		
	public function setTraitementLot($id_e,$id_d,$id_u,$action){
		$infoDocument = $this->objectInstancier->Document->getInfo($id_d);
		
		$job = new Job();
		$job->type = Job::TYPE_TRAITEMENT_LOT;
		$job->id_e = $id_e;
		$job->id_d = $id_d;
		$job->id_u = $id_u;
		$job->last_message = "Action programmé sur le document";
		$job->etat_source = $this->objectInstancier->DocumentActionEntite->getLastAction($id_e, $id_d);
		$job->etat_cible = $action;
		
		$this->getJobQueueSQL()->addJob($job);
	}
	
	
	public function setJobForConnecteur($id_ce,$last_message){
		$info = $this->objectInstancier->ConnecteurEntiteSQL->getInfo($id_ce);
		
		$job = new Job();
		$job->type = Job::TYPE_CONNECTEUR;
		$job->id_e = $info['id_e'];
		$job->id_ce = $info['id_ce'];
		$job->last_message = $last_message;
		
		
		if ($info['id_e']){
			$documentType = $this->objectInstancier->DocumentTypeFactory->getEntiteDocumentType($info['id_connecteur']);
		} else {
			$documentType = $this->objectInstancier->DocumentTypeFactory->getGlobalDocumentType($info['id_connecteur']);
		}

		$all_action = $documentType->getAction()->getAutoAction();
		foreach($all_action as $action){
			$job->etat_source = $action;
			$job->etat_cible = $action;
			$this->getJobQueueSQL()->addJob($job);
		}
		
	}
	
	public function deleteConnecteur($id_ce){
		$this->getJobQueueSQL()->deleteConnecteur($id_ce);
	}
	
	public function jobMaster(){
		$this->jobMasterMessage("job master starting");
		while(true){
			$this->jobMasterOneRun();
			sleep(1);
		}
	}
	
	public function jobMasterOneRun(){
		$workerSQL = $this->getWorkerSQL();
		
		foreach($workerSQL->getAllRunningWorker() as $info){
			if (! posix_getpgid($info['pid'])){
				$this->getJobQueueSQL()->lock($info['id_job']);				
				$workerSQL->error($info['id_worker'], "Message du job master : ce worker ne s'est pas terminé correctement");
			}
		}
		$nb_worker_alive = count($workerSQL->getAllRunningWorker());
		
		$nb_worker_to_launch = NB_WORKERS - $nb_worker_alive;
		
		$job_id_list = $workerSQL->getJobToLaunch($nb_worker_to_launch);
		
		foreach($job_id_list as $id_job){
			$this->launchWorker($id_job);
		}
	}
	
	private function launchWorker($id_job){
		$job = $this->getJobQueueSQL()->getJob($id_job);
		
		//Le master lock le job jusqu'à ce que son worker le délock pour éviter que le master ne sélectionne à nouveau ce job (si le lancement du worker est plus lent que la boucle du master)
		$this->getJobQueueSQL()->lock($id_job);
		
		$script = realpath(__DIR__."/../batch/pastell-job-worker.php");
		$command = "nohup " . PHP_PATH . " $script $id_job > /tmp/toto 2>&1 &";
		$this->jobMasterMessage("starting worker for job #$id_job : {$job->asString()}");
		exec($command);
	}
	
	private function jobMasterMessage($message){
		$date = date("Y-m-d H:i:s");
		echo "[$date] $message\n";
	}
	
	public function runningWorker(){
		try {
			$this->runningWorkerThrow();
		} catch(Exception $e){
			echo "Erreur : ".$e->getMessage()."\n";
			return;
		}
	}
	
	private function runningWorkerThrow(){		
		$id_job = get_argv(1);
		if (! $id_job){
			global $argv;
			throw new Exception("Usage : {$argv[0]} id_job");
		}
		
		$job = $this->getJobQueueSQL()->getJob($id_job);
		if (! $job){
			throw new Exception("Aucun job trouvé pour l'id_job $id_job");
		}
		
		if (! $job->isTypeOK()){
			throw new Exception("Ce type de job n'est pas traité par ce worker");
		}
		
		$workerSQL = $this->getWorkerSQL();
		
		$another_worker_info = $workerSQL->getRunningWorkerInfo($id_job);
		if ($another_worker_info){
			throw new Exception("Le job $id_job est déjà attaché au worker  #{$another_worker_info['id_worker']}");
		}
		
		$pid = getmypid();
		$id_worker = $workerSQL->create($pid);
		$workerSQL->attachJob($id_worker,$id_job);
		$this->getJobQueueSQL()->unlock($id_job);
		
		if ($job->type == Job::TYPE_DOCUMENT){
			$this->objectInstancier->ActionExecutorFactory->executeOnDocument($job->id_e,$job->id_u,$job->id_d,$job->etat_cible,array(),true, array(),$id_worker);
		} elseif($job->type == Job::TYPE_TRAITEMENT_LOT) {			
			$result = $this->objectInstancier->ActionExecutorFactory->executeOnDocument($job->id_e,$job->id_u,$job->id_d,$job->etat_cible,array(),true, array(),$id_worker);
			if (!$result){
				$info = $this->objectInstancier->Document->getInfo($job->id_d);
				$message = "Echec de l'execution de l'action dans la cadre d'un traitement par lot : ".$this->objectInstancier->ActionExecutorFactory->getLastMessage();
				echo $message;
				$this->objectInstancier->NotificationMail->notify($job->id_e,$job->id_d,$job->etat_cible,$info['type'],$message);
			}
			
		} elseif($job->type == Job::TYPE_CONNECTEUR){
			$this->objectInstancier->ActionExecutorFactory->executeOnConnecteur($job->id_ce,$job->id_e,$job->etat_cible, true, array());
		} else {
			throw new Exception("Type de job {$job->type} inconnu");
		}
		
		$workerSQL->success($id_worker);
	}

	public function hasActionProgramme($id_e,$id_d){
		return $this->getJobQueueSQL()->hasDocumentJob($id_e,$id_d);
	}
	
	public function getActionEnCours($id_e,$id_d){
		return $this->getWorkerSQL()->getActionEnCours($id_e, $id_d);
	}
	
	
	/**
	 * @return WorkerSQL
	 */
	private function getWorkerSQL(){
		return $this->objectInstancier->WorkerSQL;
	}
	
	/**
	 * @return JobQueueSQL
	 */
	private function getJobQueueSQL(){
		return $this->objectInstancier->JobQueueSQL;
	}
	
	
	
	
}