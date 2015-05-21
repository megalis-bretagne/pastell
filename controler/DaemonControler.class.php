<?php
class DaemonControler extends PastellControler {
	
	public function indexAction(){
		$this->indexData();
		
		$this->template_milieu = "DaemonIndex";
		$this->page_title = "Démon Pastell";
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
			$this->LastMessage->setLastMessage("Le démon Pastell a été démarré");
		} else {
			$this->LastError->setLastMessage("Une erreur s'est produite lors de la tentative de démarrage du démon Pastell");
		}
		$this->redirect("daemon/index.php");
	}
	
	public function daemonStop(){
		$this->verifDroit(0,"system:edition");
		$this->DaemonManager->stop();
		if ($this->DaemonManager->status() == DaemonManager::IS_STOPPED){
			$this->LastMessage->setLastMessage("Le démon Pastell a été arrêté");
		} else {
			$this->LastError->setLastMessage("Une erreur s'est produite lors de la tentative d'arrêt du démon Pastell");
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