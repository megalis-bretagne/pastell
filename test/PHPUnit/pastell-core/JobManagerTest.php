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

		$this->jobManager->setJobForConnecteur(13,"message");

		$result = $this->jobQueueSQL->query("SELECT * FROM job_queue");

		print_r($result);


	}


}