<?php
class DaemonControler extends PastellControler {

	/**
	 * @return DaemonManager
	 */
	public function getDaemonManager(){
		return $this->getObjectInstancier()->getInstance('DaemonManager');
	}

	public function indexAction(){
		$this->indexData();
		$this->page_url = "index";
		$this->template_milieu = "DaemonIndex";
		$this->page_title = "Démon Pastell";
		$this->renderDefault();
	}
	
	public function indexContentAction(){
		$this->indexData();
		header("Content-type: text/html; charset=utf-8;");
		$this->render("DaemonIndexContent");
	}
	
	private function indexData(){
		$this->verifDroit(0,"system:lecture");
		$this->nb_worker_actif = $this->WorkerSQL->getNbActif();
		$this->job_stat_info = $this->JobQueueSQL->getStatInfo();
		$this->daemon_pid = $this->getDaemonManager()->getDaemonPID();
		$this->pid_file = PID_FILE;
		
		$this->return_url = urlencode("Daemon/index");
		$this->job_list = $this->WorkerSQL->getJobListWithWorker();
	}
	
	public function daemonStartAction(){
		$this->verifDroit(0,"system:edition");
		try {
			$this->getDaemonManager()->start();
		} catch (Exception $e){
			$this->LastError->setLastMessage($e->getMessage());
			$this->redirect("Daemon/index");
		}
		if ($this->getDaemonManager()->status() == DaemonManager::IS_RUNNING){
			$this->LastMessage->setLastMessage("Le démon Pastell a été démarré");
		} else {
			$this->LastError->setLastMessage("Une erreur s'est produite lors de la tentative de démarrage du démon Pastell");
		}
		$this->redirect("Daemon/index");
	}
	
	public function daemonStopAction(){
		$this->verifDroit(0,"system:edition");
		$this->getDaemonManager()->stop();
		if ($this->getDaemonManager()->status() == DaemonManager::IS_STOPPED){
			$this->LastMessage->setLastMessage("Le démon Pastell a été arrêté");
		} else {
			$this->LastError->setLastMessage("Une erreur s'est produite lors de la tentative d'arrêt du démon Pastell");
		}
		$this->redirect("Daemon/index");
	}

	public function lockAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_job = $recuperateur->getInt('id_job');
		$return_url = $recuperateur->get('return_url','Daemon/index');
		
		$this->JobQueueSQL->lock($id_job);
		$this->redirect("$return_url");
	}
	
	public function unlockAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_job = $recuperateur->getInt('id_job');
		$return_url = $recuperateur->get('return_url','Daemon/index');
		
		$this->WorkerSQL->menage($id_job);
		$this->JobQueueSQL->unlock($id_job);
		$this->redirect($return_url);
	}

	public function unlockAllAction(){
		$this->WorkerSQL->menageAll();
		$this->JobQueueSQL->unlockAll();
		$this->verifDroit(0,"system:edition");
		$this->redirect('Daemon/index');
	}

	

	public function killAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_worker = $recuperateur->getInt('id_worker');
		$return_url = $recuperateur->get('return_url','Daemon/index');
		
		$info = $this->WorkerSQL->getInfo($id_worker);
		if (!$info){
			$this->LastError->setLastError("Ce worker n'existe pas ou plus");
			$this->redirect("$return_url");
		}
		
		$this->JobQueueSQL->lock($info['id_job']);
		posix_kill($info['pid'],SIGKILL);
		$this->workerSQL->error($info['id_worker'], "Worker tué manuellement");
		
		$this->LastMessage->setLastMessage("Le worker a été tué");
		$this->redirect("$return_url");
	}
	
	public function jobAction(){
		$this->verifDroit(0,"system:edition");
		$this->template_milieu = "DaemonJob";
		$this->page_title = "Démon Pastell";
		$recuperateur = new Recuperateur($_GET);
		$filtre = $recuperateur->get('filtre','');
		if ($filtre){
			$this->page_url = "job?filtre=$filtre";
			
		} else {
			$this->page_url = "job";
		}
		
		$this->offset = $recuperateur->getInt('offset',0);
		$this->limit = 50;
		$this->filtre = $filtre;
		
		$this->return_url = urlencode("Daemon/job?filtre=$filtre&offset=".$this->offset);
		
		$this->count = $this->WorkerSQL->getNbJob($filtre);
		$this->job_list = $this->WorkerSQL->getJobListWithWorker($this->offset,$this->limit,$filtre);
		
		$this->renderDefault();
	}
	
	
}