<?php

class WorkerSQLTest extends PastellTestCase {

	/** @var  WorkerSQL */
	private $workerSQL;

	protected function setUp(){
		parent::setUp();
		$this->workerSQL = new WorkerSQL($this->getSQLQuery());
	}

	public function testCreate(){
		$this->assertNotNull($this->workerSQL->create(42));
	}

	public function testGetInfo(){
		$id_worker = $this->workerSQL->create(42);
		$info = $this->workerSQL->getInfo($id_worker);
		$this->assertEquals(42,$info['pid']);
	}

	public function testError(){
		$id_worker = $this->workerSQL->create(42);
		$this->workerSQL->error($id_worker,"Message d'erreur");
		$info = $this->workerSQL->getInfo($id_worker);
		$this->assertEquals(1,$info['termine']);
	}

	public function testRunningWorkerInfo(){
		$id_worker = $this->workerSQL->create(42);
		$this->workerSQL->attachJob($id_worker,12);
		$info = $this->workerSQL->getRunningWorkerInfo(12);
		$this->assertEquals(12,$info['id_job']);
	}

	public function testSuccess(){
		$id_worker = $this->workerSQL->create(42);
		$this->workerSQL->success($id_worker);
		$this->assertEmpty($this->workerSQL->getInfo($id_worker));
	}

	public function testGetAllRunningWorker(){
		$id_worker = $this->workerSQL->create(42);
		$all_info = $this->workerSQL->getAllRunningWorker();
		$this->assertEquals($id_worker,$all_info[0]['id_worker']);
	}

	public function testGetJobToLauchLimit(){
		$this->assertEmpty($this->workerSQL->getJobToLaunch(0));
	}

	private function createJob(){
		$jobQueueSQL = new JobQueueSQL($this->getSQLQuery());
		$job = new Job();
		$job->type = Job::TYPE_DOCUMENT;
		$job->etat_source = "source";
		$job->etat_cible = "cible";
		$id_job = $jobQueueSQL->addJob($job);
		return $id_job;
	}

	private function launchWorker(){
		$id_job = $this->createJob();
		$id_worker = $this->workerSQL->create(42);
		$this->workerSQL->attachJob($id_worker,$id_job);
		return $id_worker;
	}

	public function testGetJobToLauch(){
		$id_job = $this->createJob();
		$id_worker = $this->workerSQL->create(42);

		$id_job_list = $this->workerSQL->getJobToLaunch(5);
		$this->assertEquals(array($id_job),$id_job_list);

		$this->workerSQL->attachJob($id_worker,$id_job);
		$this->assertEmpty($this->workerSQL->getJobToLaunch(5));
	}

	public function testGetNbActif(){
		$this->launchWorker();
		$this->assertEquals(1,$this->workerSQL->getNbActif());
	}

	public function testGetActif(){
		$id_worker = $this->launchWorker();
		$info = $this->workerSQL->getActif();
		$this->assertEquals($id_worker,$info[0]['id_worker']);
	}

	public function testGetJobListWithWorker(){
		$id_worker = $this->launchWorker();
		$info = $this->workerSQL->getJobListWithWorker(0,20,'toto');
		$this->assertEquals($id_worker,$info[0]['id_worker']);
		$this->assertEquals(1,$this->workerSQL->getNbJob('toto'));
	}

	public function testGetJobLock(){
		$this->launchWorker();
		$info = $this->workerSQL->getJobListWithWorker(0,20,'lock');
		$this->assertEmpty($info);
		$this->assertEquals(0,$this->workerSQL->getNbJob('lock'));
	}

	public function testGetJobWait(){
		$id_worker = $this->launchWorker();
		$info = $this->workerSQL->getJobListWithWorker(0,20,'wait');
		$this->assertEquals($id_worker,$info[0]['id_worker']);
		$this->assertEquals(1,$this->workerSQL->getNbJob('wait'));
	}

	public function testGetJobActif(){
		$id_worker = $this->launchWorker();
		$info = $this->workerSQL->getJobListWithWorker(0,20,'actif');
		$this->assertEquals($id_worker,$info[0]['id_worker']);
		$this->assertEquals(1,$this->workerSQL->getNbJob('actif'));
	}

	public function testMenage(){
		$this->workerSQL->menage(42);
	}

	public function testGetJobListWithWorkerForConnecteur(){
		$this->assertEmpty($this->workerSQL->getJobListWithWorkerForConnecteur(11));
	}

	public function testGetJobListWithWorkerForDocument(){
		$this->assertEmpty($this->workerSQL->getJobListWithWorkerForDocument(42,8));
	}

	public function testGetActionEnCours(){
		$this->assertEmpty($this->workerSQL->getActionEnCours(42,8));
	}

	public function testNoLaunchWithIdVerrou(){
		$jobQueueSQL = new JobQueueSQL($this->getSQLQuery());
		$job = new Job();
		$job->type = Job::TYPE_DOCUMENT;
		$job->etat_source = "source";
		$job->etat_cible = "cible";
		$job->id_d = "XYZT";
		$job->id_e = 1;
		$job->id_verrou = "VERROU";
		$id_job_1 = $jobQueueSQL->addJob($job);

		$id_job_list = $this->workerSQL->getJobToLaunch(5);
		$this->assertEquals(array($id_job_1),$id_job_list);

		$id_worker = $this->workerSQL->create(42);
		$this->workerSQL->attachJob($id_worker,$id_job_1);

		$all_verrou = $this->workerSQL->getVerrou();
		$this->assertEquals(array("VERROU"),$all_verrou);

		$job->id_d="ABCD";
		$jobQueueSQL->addJob($job);
		$id_job_list = $this->workerSQL->getJobToLaunch(5);
		$this->assertEmpty($id_job_list);
	}

	public function testNoLaunchSimultaneousWithIdVerrou(){
		$jobQueueSQL = new JobQueueSQL($this->getSQLQuery());
		$job = new Job();
		$job->type = Job::TYPE_DOCUMENT;
		$job->etat_source = "source";
		$job->etat_cible = "cible";
		$job->id_d = "XYZT";
		$job->id_e = 1;
		$job->id_verrou = "VERROU";
		$id_job_1 = $jobQueueSQL->addJob($job);

		$job->id_d="ABCD";
		$jobQueueSQL->addJob($job);

		$id_job_list = $this->workerSQL->getJobToLaunch(5);
		$this->assertEquals(array($id_job_1),$id_job_list);
	}

	private function addJobWithVerrou(){
		$jobQueueSQL = new JobQueueSQL($this->getSQLQuery());
		$job = new Job();
		$job->type = Job::TYPE_DOCUMENT;
		$job->etat_source = "source";
		$job->etat_cible = "cible";
		$job->id_d = "XYZT";
		$job->id_e = 1;
		$job->id_verrou = "VERROU";
		$jobQueueSQL->addJob($job);
	}

	public function testGetAllVerrou(){
		$this->addJobWithVerrou();
		$all_verrou = $this->workerSQL->getAllVerrou();
		$this->assertEquals(array("VERROU"),$all_verrou);
	}

	public function testGetFirstJobToLaunch(){
		$this->addJobWithVerrou();
		$job = $this->workerSQL->getFirstJobToLaunch("VERROU");
		$this->assertEquals(1,$job['id_job']);
	}

	public function testGetJobToLaunch(){
		$this->createJob();
		$this->addJobWithVerrou();
		$job_list = $this->workerSQL->getJobToLaunch(4);
		$this->assertEquals(array("2","1"),$job_list);
	}

}
