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
	
	
	public function sig_handler(){
		echo "SIGUSER received";
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
				$workerSQL->error($info['id_worker'], "Message du job master : ce worker ne s'est pas termin� correctement");
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
		
		//Le master lock le job jusqu'� ce que son worker le d�lock pour �viter que le master ne s�lectionne � nouveau se job (si le lancement du worker est plus lent que la boucle du master)
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
		$pid = getmypid();
		$workerSQL = $this->getWorkerSQL();
	
		$id_worker = $workerSQL->create($pid);
		try {
			$this->runningWorkerThrow($id_worker);
		} catch(Exception $e){
			$workerSQL->error($id_worker,$e->getMessage());
			return;
		}
	}
	
	private function runningWorkerThrow($id_worker){
		$workerSQL = $this->getWorkerSQL();
		
		$id_job = get_argv(1);
		if (! $id_job){
			throw new Exception("Aucun id_job donn�e");
		}
				
		$another_worker_info = $workerSQL->getRunningWorkerInfo($id_job);
		
		if (! posix_getpgid($another_worker_info['pid'])){
			$workerSQL->error($another_worker_info['id_worker'], "Arret� par un autre worker (#$id_worker) : pas de processus trouv�");
			$another_worker_info = false;
		}
		
		if ($another_worker_info){
			throw new Exception("Le job $id_job est d�j� attach� au worker  #{$another_worker_info['id_worker']}");
		}
		
		$workerSQL->attachJob($id_worker,$id_job);
		
		//Le worker d�lock le job : celui-ci ne sera plus s�lectionn� par le master apr�s l'attachement 
		$this->getJobQueueSQL()->unlock($id_job);
		
				
		$job = $this->getJobQueueSQL()->getJob($id_job);
		if (! $job){
			throw new Exception("Aucun job trouv� pour l'id $id_job");
		}
		
		if ($job->type != Job::TYPE_DOCUMENT){
			throw new Exception("Ce type de job n'est pas trait� par ce worker");
		}
		
		$this->objectInstancier->ActionExecutorFactory->executeOnDocument($job->id_e,$job->id_u,$job->id_d,$job->etat_cible,array(),true, array());
		
		$workerSQL->success($id_worker);
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