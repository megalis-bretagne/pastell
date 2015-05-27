<?php
class DaemonControler extends PastellControler {
	
	public function indexAction(){
		$this->indexData();
		$this->page_url = "index.php";
		
		$this->template_milieu = "DaemonIndex";
		$this->page_title = "D�mon Pastell";
		$this->renderDefault();
	}
	
	public function indexContentAction(){
		$this->indexData();
		header("Content-type: text/html; charset=iso-8859-15;");
		$this->render("DaemonIndexContent");
	}
	
	private function indexData(){
		$this->verifDroit(0,"system:lecture");
		$this->nb_worker_actif = $this->WorkerSQL->getNbActif();
		$this->job_stat_info = $this->JobQueueSQL->getStatInfo();
		$this->daemon_pid = $this->DaemonManager->getDaemonPID();
		$this->return_url = urlencode("index.php");
		$this->job_list = $this->WorkerSQL->getJobListWithWorker();
	}
	
	public function daemonStart(){
		$this->verifDroit(0,"system:edition");
		$this->DaemonManager->start();
		if ($this->DaemonManager->status() == DaemonManager::IS_RUNNING){
			$this->LastMessage->setLastMessage("Le d�mon Pastell a �t� d�marr�");
		} else {
			$this->LastError->setLastMessage("Une erreur s'est produite lors de la tentative de d�marrage du d�mon Pastell");
		}
		$this->redirect("daemon/index.php");
	}
	
	public function daemonStop(){
		$this->verifDroit(0,"system:edition");
		$this->DaemonManager->stop();
		if ($this->DaemonManager->status() == DaemonManager::IS_STOPPED){
			$this->LastMessage->setLastMessage("Le d�mon Pastell a �t� arr�t�");
		} else {
			$this->LastError->setLastMessage("Une erreur s'est produite lors de la tentative d'arr�t du d�mon Pastell");
		}
		$this->redirect("daemon/index.php");
	}

	public function lockAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_job = $recuperateur->getInt('id_job');
		$return_url = $recuperateur->get('return_url','index.php');
		
		
		$this->JobQueueSQL->lock($id_job);
		$this->redirect("daemon/$return_url");
	}
	
	public function unlockAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_job = $recuperateur->getInt('id_job');
		$return_url = $recuperateur->get('return_url','index.php');
		
		$this->WorkerSQL->menage($id_job);
		$this->JobQueueSQL->unlock($id_job);
		$this->redirect("daemon/$return_url");
	}
	

	public function killAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_worker = $recuperateur->getInt('id_worker');
		$return_url = $recuperateur->get('return_url','index.php');
		
		$info = $this->WorkerSQL->getInfo($id_worker);
		if (!$info){
			$this->LastError->setLastError("Ce worker n'existe pas ou plus");
			$this->redirect("daemon/$return_url");
		}
		
		$this->JobQueueSQL->lock($info['id_job']);
		posix_kill($info['pid'],SIGKILL);
		$this->workerSQL->error($info['id_worker'], "Worker tu� manuellement");
		
		$this->LastMessage->setLastMessage("Le worker a �t� tu�");
		$this->redirect("daemon/$return_url");
	}
	
	public function jobAction(){
		$this->verifDroit(0,"system:edition");
		$this->template_milieu = "DaemonJob";
		$this->page_title = "D�mon Pastell";
		$recuperateur = new Recuperateur($_GET);
		$filtre = $recuperateur->get('filtre','');
		if ($filtre){
			$this->page_url = "job.php?filtre=$filtre";
			
		} else {
			$this->page_url = "job.php";
		}
		
		$this->offset = $recuperateur->getInt('offset',0);
		$this->limit = 50;
		$this->filtre = $filtre;
		
		$this->return_url = urlencode("job.php?filtre=$filtre&offset=".$this->offset);
		
		$this->count = $this->WorkerSQL->getNbJob($filtre);
		$this->job_list = $this->WorkerSQL->getJobListWithWorker($this->offset,$this->limit,$filtre);
		
		$this->renderDefault();
	}
	
	
}