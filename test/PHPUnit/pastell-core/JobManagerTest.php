<?php

class JobManagerTest extends PastellTestCase {

	/** @var  JobManager */
	private $jobManager;

	/** @var  JobQueueSQL */
	private $jobQueueSQL;

	protected function setUp() {
		parent::setUp();
		$this->jobManager = $this->getObjectInstancier()->getInstance("JobManager");
		$this->jobQueueSQL = $this->getObjectInstancier()->getInstance("JobQueueSQL");
	}

	public function testSetJobForConnecteur(){
		$id_job = $this->jobManager->setJobForConnecteur(13,"une_action_auto","message");
		$job = $this->jobQueueSQL->getJob($id_job);
		$this->assertTrue($job->isTypeOK());
		$this->assertEquals(Job::TYPE_CONNECTEUR,$job->type);
		$this->assertEquals(13,$job->id_ce);
		$this->assertEquals(1,$job->id_e);
	}

	public function testSetJobForConnecteurUpdate(){
		$id_job = $this->jobManager->setJobForConnecteur(13,"une_action_auto","message");
		$this->jobQueueSQL->getJob($id_job);
		$id_job_2 = $this->jobManager->setJobForConnecteur(13,"une_action_auto","message 2");
		$this->assertEquals($id_job,$id_job_2);
		$job = $this->jobQueueSQL->getJob($id_job);
		$this->assertTrue($job->isTypeOK());
		$this->assertEquals(Job::TYPE_CONNECTEUR,$job->type);
		$this->assertEquals(13,$job->id_ce);
		$this->assertEquals(1,$job->id_e);
		$this->assertEquals("message 2",$job->last_message);
		$this->assertEquals(1,$job->nb_try);
	}

	public function testSetJobForDocument(){
		$info = $this->getInternalAPI()->post("Entite/1/Document",array('type'=>'test'));
		$id_d = $info['info']['id_d'];
		$this->getInternalAPI()->post("Entite/1/Document/$id_d/action/action-auto");
		$id_job = $this->jobManager->setJobForDocument(1,$id_d,"test");
		$job = $this->jobQueueSQL->getJob($id_job);
		$this->assertEquals($id_d,$job->id_d);
		$this->assertEquals('action-auto',$job->etat_source);
	}

	public function testSetJobForTraitementLot(){
		$info = $this->getInternalAPI()->post("Entite/1/Document",array('type'=>'test'));
		$id_d = $info['info']['id_d'];
		$id_job = $this->jobManager->setTraitementLot(1,$id_d,0,'ok');
		$job = $this->jobQueueSQL->getJob($id_job);
		$this->assertEquals($id_d,$job->id_d);
		$this->assertEquals('ok',$job->etat_cible);
	}



}