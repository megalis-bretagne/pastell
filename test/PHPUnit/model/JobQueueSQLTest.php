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
		$this->jobQueueSQL->createJob($this->job);
	}

	public function testAddJobNoCible(){
		$this->job->type = Job::TYPE_DOCUMENT;
		$this->job->etat_cible = "cible";
		$this->job->etat_source = "source";
		$id_job = $this->jobQueueSQL->createJob($this->job);
		$this->assertNotEquals(0,$id_job);
	}

	public function testAddJob(){
		$job = new Job();
		$job->type = Job::TYPE_DOCUMENT;
		$job->etat_cible = "cible";
		$job->etat_source = "source";
		$job->id_verrou = "VERROU";
		$id_job = $this->jobQueueSQL->createJob($job);
		$job_result = $this->jobQueueSQL->getJob($id_job);
		$this->assertEquals("VERROU",$job_result->id_verrou);
	}



}