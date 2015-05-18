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
		
		//TODO Voir s'il y a un nouveau job et réveillez le queue master si oui
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
	
	public function jobMaster(){
		//TODO : ajouter un gestionnaire de signal
		while(true){
			$this->jobMasterOneRun();
			sleep(1);
		}
	}
	
	
	public function jobMasterOneRun(){
		$workerSQL = $this->getWorkerSQL();
		
		foreach($workerSQL->getAllRunningWorker() as $info){
			if (! posix_getpgid($info['pid'])){
				$workerSQL->error($info['id_worker'], "Message du job Master : ce worker ne s'est pas terminé correctement");
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
		$script = realpath(__DIR__."/../batch/pastell-job-worker.php");
		$command = "nohup " . PHP_PATH . " $script $id_job > /tmp/toto 2>&1 &";
		echo $command."\n";
		exec($command);
	}
	
	
	private function runningWorkerThrow($id_worker){
		$workerSQL = $this->getWorkerSQL();
		
		$id_job = get_argv(1);
		if (! $id_job){
			throw new Exception("Aucun id_job donnée");
		}
				
		$another_worker_info = $workerSQL->getRunningWorkerInfo($id_job);
		
		if (! posix_getpgid($another_worker_info['pid'])){
			$workerSQL->error($another_worker_info['id_worker'], "Arreté par un autre worker (#$id_worker) : pas de processus trouvé");
			$another_worker_info = false;
		}
		
		if ($another_worker_info){
			throw new Exception("Le job $id_job est déjà attaché au worker  #{$another_worker_info['id_worker']}");
		}
		
		$workerSQL->attachJob($id_worker,$id_job);
		//TODO déloker le master
				
		$job = $this->getJobQueueSQL()->getJob($id_job);
		if (! $job){
			throw new Exception("Aucun job trouvé pour l'id $id_job");
		}
		
		if ($job->type != Job::TYPE_DOCUMENT){
			throw new Exception("Ce type de job n'est pas traité par ce worker");
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