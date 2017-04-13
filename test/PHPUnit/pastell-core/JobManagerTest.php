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
		$id_job = $this->jobQueueSQL->getJobIdForDocument(1,$id_d);
		$job = $this->jobQueueSQL->getJob($id_job);
		$this->assertEquals(0,$job->nb_try);
		$this->assertEquals('DEFAULT_FREQUENCE',$job->id_verrou);

		$id_job = $this->jobManager->setJobForDocument(1,$id_d,"test");
		$job = $this->jobQueueSQL->getJob($id_job);
		$this->assertEquals($id_d,$job->id_d);
		$this->assertEquals('action-auto',$job->etat_source);
		$this->assertEquals(1,$job->nb_try);
		$this->assertEquals('DEFAULT_FREQUENCE',$job->id_verrou);
		$this->assertTrue(strtotime("+2 minutes") - strtotime($job->next_try)<1);
	}

	public function testSetJobForTraitementLot(){
		$info = $this->getInternalAPI()->post("Entite/1/Document",array('type'=>'test'));
		$id_d = $info['info']['id_d'];
		$id_job = $this->jobManager->setTraitementLot(1,$id_d,0,'ok');
		$job = $this->jobQueueSQL->getJob($id_job);
		$this->assertEquals($id_d,$job->id_d);
		$this->assertEquals('ok',$job->etat_cible);
	}

	public function testJobEnding(){
		$info = $this->getInternalAPI()->post("Entite/1/Document",array('type'=>'test'));
		$id_d = $info['info']['id_d'];
		$this->getInternalAPI()->post("Entite/1/Document/$id_d/action/action-auto");
		$this->getInternalAPI()->post("Entite/1/Document/$id_d/action/action-auto-end");
		$this->assertFalse($this->jobQueueSQL->getJobIdForDocument(1,$id_d));
	}

	public function testDisableJobQueue(){
		$this->jobManager->setDisableJobQueue(true);
		$this->assertTrue($this->jobManager->setJobForConnecteur(42,'toto','blutrepoi'));
		$this->assertTrue($this->jobManager->setJobForDocument(42,42,'blutrepoi'));
		$this->assertTrue($this->jobManager->setTraitementLot(42,42,42,'blutrepoi'));
	}

	public function testHasActionProgramme(){
		$info = $this->getInternalAPI()->post("Entite/1/Document",array('type'=>'test'));
		$id_d = $info['info']['id_d'];
		$this->jobManager->setTraitementLot(1,$id_d,0,'ok');
		$this->assertTrue($this->jobManager->hasActionProgramme(1,$id_d));
	}

	public function testDeleteConnecteur(){
		$id_job = $this->jobManager->setJobForConnecteur(13,"une_action_auto","message");
		$this->assertNotNull($this->jobQueueSQL->getJob($id_job));
		$this->jobManager->deleteConnecteur(13);
		$this->assertNull($this->jobQueueSQL->getJob($id_job));
	}

	public function testChainedAction(){
		$info = $this->getInternalAPI()->post("Entite/1/Document",array('type'=>'test'));
		$id_d = $info['info']['id_d'];
		$this->getInternalAPI()->post("Entite/1/Document/$id_d/action/chained-action-1");
		$this->getInternalAPI()->post("Entite/1/Document/$id_d/action/chained-action-2");
		$id_job = $this->jobQueueSQL->getJobIdForDocument(1,$id_d);
		$job = $this->jobQueueSQL->getJob($id_job);
		$this->assertEquals("chained-action-3",$job->etat_cible);
		$this->assertEquals("chained-action-2",$job->etat_source);
		$this->assertEquals(0,$job->nb_try);
	}
}