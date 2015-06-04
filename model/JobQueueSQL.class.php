<?php
class JobQueueSQL extends SQL {

	public function addJob(Job $job){
		if (! $job->isTypeOK()){
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
	
	public function deleteConnecteur($id_ce){
		if ($id_ce == 0){
			return;
		}
		$sql = "DELETE FROM job_queue WHERE id_ce=?";
		$this->query($sql,$id_ce);
	}
	
	private function deleteJob(Job $job){
		$sql = "SELECT id_job FROM job_queue WHERE id_e=? AND id_d=?";
		$id_job = $this->queryOne($sql,$job->id_e,$job->id_d);
				
		$sql = "DELETE FROM job_queue WHERE id_job=?";
		$this->query($sql,$id_job);
	}
			
	private function getInfoFromDocument(Job $job){
		$sql = "SELECT * FROM job_queue " . 
				" WHERE id_e=? AND id_d=? ";
		return $this->queryOne($sql,$job->id_e,$job->id_d);
	}
	
	private function createJob(Job $job){
		$sql = "INSERT INTO job_queue(type,id_e,id_d,id_u,etat_source,etat_cible,id_ce) VALUES (?,?,?,?,?,?,?)";
		$this->query($sql,$job->type,$job->id_e,$job->id_d,$job->id_u,$job->etat_source,$job->etat_cible,$job->id_ce);
		
		$id_job = $this->lastInsertId();
		return $id_job; 
	}
	
	private function updateSameJob(Job $job,array $job_info){
		$now = date('Y-m-d H:i:s');
		$next_try = date('Y-m-d H:i:s',strtotime("+ {$job->next_try_in_minutes} minutes"));
		if ($job_info['nb_try'] == 0){
			$sql = "UPDATE job_queue SET first_try=?,last_try=?,nb_try=?,next_try=? WHERE id_job=?" ;
			$this->query($sql,$now,$now,1,$next_try,$job_info['id_job']);
		} else {
			$sql = "UPDATE job_queue SET last_try=?,nb_try=?,next_try=? WHERE id_job=?" ;
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
				" WHERE job_queue.id_job=? ";
		$info =  $this->queryOne($sql,$id_job);
		if (! $info){
			return false;
		}
		$job = new Job();
		$job->id_e = $info['id_e'];
		$job->id_d = $info['id_d'];
		$job->id_u = $info['id_u'];
		$job->id_ce = $info['id_ce'];
		$job->etat_source = $info['etat_source'];
		$job->etat_cible = $info['etat_cible'];
		$job->type = $info['type'];
		$job->last_message = $info['last_message'];
		$job->is_lock = $info['is_lock'];
		return $job;
	}
	
	public function lock($id_job){
		$sql = "UPDATE job_queue SET is_lock=1,lock_since=now() WHERE id_job=?";
		$this->query($sql,$id_job);
	}
	
	public function unlock($id_job){
		$sql = "UPDATE job_queue SET is_lock=0 WHERE id_job=?";
		$this->query($sql,$id_job);
	}
	
	public function getStatInfo(){
		$sql = "SELECT count(*) FROM job_queue";
		$info['nb_job'] = $this->queryOne($sql);
		
		$sql = "SELECT count(*) FROM job_queue WHERE is_lock=1";
		$info['nb_lock'] = $this->queryOne($sql);
		
		$sql = "SELECT count(*) FROM job_queue " .
				" WHERE next_try<now()";
		$info['nb_wait'] = $this->queryOne($sql);
		
		return $info;
	}
	
	public function getJobLock(){
		$sql = "SELECT * FROM job_queue ".
				" WHERE is_lock=1" . 
				" ORDER BY lock_since" .
				" LIMIT 20 ";
		return $this->query($sql);
	}
	
	public function hasDocumentJob($id_e,$id_d){
		$sql = "SELECT count(*) FROM job_queue ".
				" WHERE id_e=? AND id_d=?";
		return $this->queryOne($sql,$id_e,$id_d);
	}
	
}