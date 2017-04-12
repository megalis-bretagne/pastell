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

		$id_job = $this->jobManager->setJobForConnecteur(13,"message");

		$job = $this->jobQueueSQL->getJob($id_job);

		print_r($job);


	}


}