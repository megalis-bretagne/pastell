<?php
class DaemonControler extends PastellControler {

	const NB_JOB_DISPLAYING = 50;

	public function _beforeAction(){
		parent::_beforeAction();
		$this->{'menu_gauche_template'} = "DaemonMenuGauche";
		$this->{'menu_gauche_select'} = "Daemon/index";
	}

	/**
	 * @return DaemonManager
	 */
	public function getDaemonManager(){
		return $this->getInstance('DaemonManager');
	}

	/**
	 * @return JobQueueSQL
	 */
	public function getJobQueueSQL(){
		return $this->getInstance('JobQueueSQL');
	}

	/** @return ConnecteurFrequenceSQL */
	public function getConnecteurFrequenceSQL(){
		return $this->getObjectInstancier()->getInstance("ConnecteurFrequenceSQL");
	}

	public function indexAction(){
		$this->indexData();
		$this->{'page_url'} = "index";
		$this->{'template_milieu'} = "DaemonIndex";
		$this->{'page_title'} = "Démon Pastell";
		$this->renderDefault();
	}
	
	public function indexContentAction(){
		$this->indexData();
		header("Content-type: text/html; charset=utf-8;");
		$this->render("DaemonIndexContent");
	}
	
	private function indexData(){
		$this->verifDroit(0,"system:lecture");
		$this->{'nb_worker_actif'} = $this->getWorkerSQL()->getNbActif();
		$this->{'job_stat_info'} = $this->getJobQueueSQL()->getStatInfo();
		$this->{'daemon_pid'} = $this->getDaemonManager()->getDaemonPID();
		$this->{'pid_file'} = PID_FILE;
		
		$this->{'return_url'} = urlencode("Daemon/index");
		$this->{'job_list'} = $this->getWorkerSQL()->getJobListWithWorker();
	}
	
	public function daemonStartAction(){
		$this->verifDroit(0,"system:edition");
		try {
			$this->getDaemonManager()->start();
		} catch (Exception $e){
			$this->setLastError($e->getMessage());
			$this->redirect("Daemon/index");
		}
		if ($this->getDaemonManager()->status() == DaemonManager::IS_RUNNING){
			$this->setLastMessage("Le démon Pastell a été démarré");
		} else {
			$this->setLastError("Une erreur s'est produite lors de la tentative de démarrage du démon Pastell");
		}
		$this->redirect("Daemon/index");
	}
	
	public function daemonStopAction(){
		$this->verifDroit(0,"system:edition");
		$this->getDaemonManager()->stop();
		if ($this->getDaemonManager()->status() == DaemonManager::IS_STOPPED){
			$this->setLastMessage("Le démon Pastell a été arrêté");
		} else {
			$this->setLastError("Une erreur s'est produite lors de la tentative d'arrêt du démon Pastell");
		}
		$this->redirect("Daemon/index");
	}

	public function lockAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_job = $recuperateur->getInt('id_job');
		$return_url = $recuperateur->get('return_url','Daemon/index');
		
		$this->getJobQueueSQL()->lock($id_job);
		$this->redirect("$return_url");
	}
	
	public function unlockAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_job = $recuperateur->getInt('id_job');
		$return_url = $recuperateur->get('return_url','Daemon/index');
		
		$this->getWorkerSQL()->menage($id_job);
		$this->getJobQueueSQL()->unlock($id_job);
		$this->redirect($return_url);
	}

	public function unlockAllAction(){
		$this->getWorkerSQL()->menageAll();
		$this->getJobQueueSQL()->unlockAll();
		$this->verifDroit(0,"system:edition");
		$this->redirect('Daemon/index');
	}

	

	public function killAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_worker = $recuperateur->getInt('id_worker');
		$return_url = $recuperateur->get('return_url','Daemon/index');
		
		$info = $this->getWorkerSQL()->getInfo($id_worker);
		if (!$info){
			$this->setLastError("Ce worker n'existe pas ou plus");
			$this->redirect("$return_url");
		}
		
		$this->getJobQueueSQL()->lock($info['id_job']);
		posix_kill($info['pid'],SIGKILL);
		$this->getWorkerSQL()->error($info['id_worker'], "Worker tué manuellement");
		
		$this->setLastMessage("Le worker a été tué");
		$this->redirect("$return_url");
	}
	
	public function jobAction(){
		$this->{'menu_gauche_select'} = "Daemon/job";

		$this->verifDroit(0,"system:edition");
		$this->{'template_milieu'} = "DaemonJob";
		$this->{'page_title'} = "Démon Pastell";
		$recuperateur = new Recuperateur($_GET);
		$filtre = $recuperateur->get('filtre','');
		if ($filtre){
			$this->{'page_url'} = "job?filtre=$filtre";
			$this->{'menu_gauche_select'} = "Daemon/job?filtre=$filtre";
		} else {
			$this->{'page_url'} = "job";
		}
		
		$this->{'offset'} = $recuperateur->getInt('offset',0);
		$this->{'limit'} = self::NB_JOB_DISPLAYING;
		$this->{'filtre'} = $filtre;
		
		$this->{'return_url'} = urlencode("Daemon/job?filtre=$filtre&offset=".$this->{'offset'});
		
		$this->{'count'} = $this->getWorkerSQL()->getNbJob($filtre);
		$this->{'job_list'} = $this->getWorkerSQL()->getJobListWithWorker($this->{'offset'},$this->{'limit'},$filtre);
		
		$this->renderDefault();
	}

	public function detailAction(){
		$this->verifDroit(0,"system:edition");
		$id_job = $this->getGetInfo()->get("id_job");

		$this->{'page_title'} = "Détail job #{$id_job}";
		/** @var JobQueueSQL $jobQueueSQL */
		$jobQueueSQL = $this->{'JobQueueSQL'};
		$this->{'job_info'} = $jobQueueSQL->getJobInfo($id_job);
		$this->{'return_url'} = "Daemon/detail?id_job=$id_job";
		$this->{'template_milieu'} = "DaemonDetail";
		$this->renderDefault();
	}

	public function configAction(){
		$this->verifDroit(0,"system:edition");

		$this->{'page_title'} = "Configuration de la fréquence des connecteurs";
		$this->{'template_milieu'} = "DaemonConfig";
		$this->{'menu_gauche_select'} = "Daemon/config";
		$this->{'nouveau_bouton_url'} = "Daemon/editFrequence";
		$this->{'connecteur_frequence_list'} = $this->getConnecteurFrequenceSQL()->getAll();
		$this->renderDefault();
	}

	public function editFrequenceAction(){
		$this->verifDroit(0,"system:edition");
		$id_cf = $this->getGetInfo()->getInt('id_cf');
		$this->{'connecteur_frequence_info'} = $this->getConnecteurFrequenceSQL()->getInfo($id_cf);
		$this->{'page_title'} = "Création d'une fréquence de connecteur";
		$this->{'template_milieu'} = "DaemmonEditFrequence";
		$this->{'menu_gauche_select'} = "Daemon/config";
		$this->renderDefault();
	}

	public function listFamilleAjaxAction(){
		print_r(json_encode($this->apiGet("/FamilleConnecteur")));
	}

	public function listConnecteurAjaxAction(){
		$connecteur = $this->getGetInfo()->get("famille_connecteur");
		$result = $this->apiGet("/FamilleConnecteur/$connecteur");
		print_r(json_encode($result));
	}

	public function listInstanceConnecteurAjaxAction(){
		$famille_connecteur = $this->getGetInfo()->get("famille_connecteur");
		$id_connecteur = $this->getGetInfo()->get("id_connecteur");
		$result = $this->apiGet("/FamilleConnecteur/$famille_connecteur/$id_connecteur");
		print_r(json_encode($result));
	}

	public function doEditFrequenceAction(){
		$this->verifDroit(0,"system:edition");

		$connecteurFrequence = new ConnecteurFrequence();

		foreach(array('type_connecteur','famille_connecteur','id_connecteur') as $key){
			$connecteurFrequence->$key = $this->getPostInfo()->get($key);
		}

		$id_cf = $this->getConnecteurFrequenceSQL()->create($connecteurFrequence);

		$this->redirect("Daemon/connecteurFrequenceDetail?id_cf=$id_cf");
	}

	public function connecteurFrequenceDetailAction(){
		$id_cf = $this->getGetInfo()->getInt('id_cf');
		$connecteur_frequence_info = $this->verifConnecteur($id_cf);
		$this->{'connecteur_frequence_info'} = $connecteur_frequence_info;
		$this->{'page_title'} = "Détail sur la fréquence d'un connecteur";
		$this->{'template_milieu'} = "DaemonFrequenceDetail";
		$this->{'menu_gauche_select'} = "Daemon/config";
		$this->renderDefault();
	}
	private function verifConnecteur($id_cf){
		$connecteur_frequence_info = $this->getConnecteurFrequenceSQL()->getInfo($id_cf);

		if (! $connecteur_frequence_info){
			$this->setLastError("Impossible de trouver le connecteur $id_cf");
			$this->redirect("Daemon/config");
		}
		return $connecteur_frequence_info;
	}
}