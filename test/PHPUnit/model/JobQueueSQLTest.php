<?php

class JobQueueSQLTest extends PastellTestCase {

	/**
	 * @var JobQueueSQL
	 */
	private $jobQueueSQL;

	/**
	 * @var Job
	 */
	private $job;

	protected function setUp(){
		parent::setUp();
		$this->jobQueueSQL = new JobQueueSQL($this->getSQLQuery());
		$this->job = new Job();
	}

	public function testAddJobNoJobConfigured(){
		$this->setExpectedException("Exception","Type de job non pris en charge");
		$this->jobQueueSQL->addJob($this->job);
	}

	public function testAddJobNoCible(){
		$this->job->type = Job::TYPE_DOCUMENT;
		$id_job = $this->jobQueueSQL->addJob($this->job);
		$this->assertEquals(0,$id_job);
	}

	public function testAddJob(){
		$job = new Job();
		$job->type = Job::TYPE_DOCUMENT;
		$job->etat_cible = "cible";
		$job->etat_source = "source";
		$job->id_verrou = "VERROU";
		$id_job = $this->jobQueueSQL->addJob($job);
		$job_result = $this->jobQueueSQL->getJob($id_job);
		$this->assertEquals("VERROU",$job_result->id_verrou);
	}

	public function testUpdateJob(){
		$job = new Job();
		$job->type = Job::TYPE_DOCUMENT;
		$job->etat_cible = "cible";
		$job->etat_source = "source";
		$job->id_e = 42;
		$job->id_d = 'foo';
		$id_job = $this->jobQueueSQL->addJob($job);


		$job->id_verrou = "VERROU";
		$id_job_update = $this->jobQueueSQL->addJob($job);
		$this->assertEquals($id_job,$id_job_update);
		$job_result = $this->jobQueueSQL->getJob($id_job);
		$this->assertEquals("VERROU",$job_result->id_verrou);

		$job->id_verrou = "VERROU_2";
		$this->jobQueueSQL->addJob($job);
		$job_result = $this->jobQueueSQL->getJob($id_job);
		$this->assertEquals("VERROU_2",$job_result->id_verrou);
	}


}