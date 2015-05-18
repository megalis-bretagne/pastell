<?php
class JobQueue {
	
	public function addJob(Job $job){
		$job_info = $this->JobQueueSQL->getJobInfo($job);
		if (! $job_info){
			$this->JobQueueSQL->newJob($job);
		}
		
	}
	
}