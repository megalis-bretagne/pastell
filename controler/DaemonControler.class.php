<?php
class DaemonControler extends PastellControler {
	
	public function indexAction(){
		$this->indexData();
		
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
		
		$this->JobQueueSQL->lock($id_job);
		$this->redirect("daemon/index.php");
	}
	
	public function unlockAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_job = $recuperateur->getInt('id_job');
		
		$this->JobQueueSQL->unlock($id_job);
		$this->redirect("daemon/index.php");
	}
	
	
}