<?php
class JobQueueSQL extends SQL {

	public function addJob(Job $job){
		if ($job->type != Job::TYPE_DOCUMENT){
			throw new Exception("Type de job non pris en charge");
		}
		if (! $job->etat_cible){
			$this->deleteJob($job);
			return;
		}
		
		$job_info = $this->getInfoFromDocument($job);
		if (! $job_info){
			$this->createJob($job);
			return;
		} 
		
		if ($job_info['etat_cible'] != $job->etat_cible){
			$this->deleteJob($job);
			$this->createJob($job);
			return;
		}
		
		$this->updateSameJob($job,$job_info);
	}
	
	private function deleteJob(Job $job){
		$sql = "SELECT id_job FROM job_queue_document WHERE id_e=? AND id_d=?";
		$id_job = $this->queryOne($sql,$job->id_e,$job->id_d);
		
		$sql = "DELETE FROM job_queue_document WHERE id_job=?";
		$this->query($sql,$id_job);
		
		$sql = "DELETE FROM job_queue WHERE id_job=?";
		$this->query($sql,$id_job);
	}
			
	private function getInfoFromDocument(Job $job){
		$sql = "SELECT * FROM job_queue " . 
				" JOIN job_queue_document ON job_queue.id_job=job_queue_document.id_job" .
				" WHERE job_queue_document.id_e=? AND job_queue_document.id_d=? ";
		return $this->queryOne($sql,$job->id_e,$job->id_d);
	}
	
	private function createJob(Job $job){
		$sql = "INSERT INTO job_queue(type) VALUES (?)";
		$this->query($sql,$job->type);
		
		$id_job = $this->lastInsertId();
		
		$sql = "INSERT INTO job_queue_document(id_job,id_e,id_d,id_u,etat_source,etat_cible) VALUES (?,?,?,?,?,?) ";
		$this->query($sql,$id_job,$job->id_e,$job->id_d,$job->id_u,$job->etat_source,$job->etat_cible); 
	}
	
	private function updateSameJob(Job $job,array $job_info){
		$now = date('Y-m-d H:i:s');
		$next_try = date('Y-m-d H:i:s',strtotime("+ {$job->next_try_in_minutes} minutes"));
		if ($job_info['nb_try'] == 0){
			$sql = "UPDATE job_queue_document SET first_try=?,last_try=?,nb_try=?,next_try=? WHERE id_job=?" ;
			$this->query($sql,$now,$now,1,$next_try,$job_info['id_job']);
		} else {
			$sql = "UPDATE job_queue_document SET last_try=?,nb_try=?,next_try=? WHERE id_job=?" ;
			$this->query($sql,$now,$job_info['nb_try'] + 1,$next_try,$job_info['id_job']);
		}
		$sql = "UPDATE job_queue set last_message=? WHERE id_job=?";
		$this->query($sql,$job->last_message,$job_info['id_job']);
	}
	
	/**
	 * 
	 * @param int $id_job
	 * @return Job
	 */
	public function getJob($id_job){
		$sql = "SELECT * FROM job_queue " . 
				" JOIN job_queue_document ON job_queue.id_job=job_queue_document.id_job" .
				" WHERE job_queue.id_job=? ";
		$info =  $this->queryOne($sql,$id_job);
		if (! $info){
			return false;
		}
		$job = new Job();
		$job->id_e = $info['id_e'];
		$job->id_d = $info['id_d'];
		$job->id_u = $info['id_u'];
		$job->etat_source = $info['etat_source'];
		$job->etat_cible = $info['etat_cible'];
		$job->type = $info['type'];
		$job->last_message = $info['last_message'];
		return $job;
	}
	
}