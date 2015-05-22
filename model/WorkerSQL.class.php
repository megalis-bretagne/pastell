<?php
class WorkerSQL extends SQL {
	
	public function create($pid){
		$sql = "INSERT INTO worker (pid,date_begin) VALUES (?,now())";
		$this->query($sql,$pid);
		return $this->lastInsertId();
	}
	
	public function error($id_worker,$message){
		$sql = "UPDATE worker SET message=?,date_end=now(),termine=1 WHERE id_worker=?";
		$this->query($sql,$message,$id_worker);
	}
	
	public function getRunningWorkerInfo($id_job){
		$sql = "SELECT * FROM worker WHERE id_job=? AND termine=0";
		return $this->queryOne($sql,$id_job);
	}
	
	public function attachJob($id_worker,$id_job){
		$sql = "UPDATE worker SET id_job=? WHERE id_worker=?";
		$this->query($sql,$id_job,$id_worker);
	}
	
	public function success($id_worker){
		$sql = "DELETE FROM worker WHERE id_worker=?";
		$this->query($sql,$id_worker);
	}
	
	public function getAllRunningWorker(){
		$sql = "SELECT * FROM worker WHERE termine=0";
		return $this->query($sql);
	}
	
	public function getJobToLaunch($limit){
		if ($limit<=0){
			return array();
		}
		$sql ="SELECT job_queue.id_job FROM job_queue " .
				" JOIN job_queue_document ON job_queue.id_job=job_queue_document.id_job" .
				" LEFT JOIN worker ON job_queue_document.id_job=worker.id_job AND worker.termine=0" .  
				" WHERE worker.id_worker IS NULL " .
				" AND next_try<now() " .
				" AND is_lock=0 " .
				" ORDER BY next_try " .
				" LIMIT $limit";
		return $this->queryOneCol($sql);
	}
	
	public function getNbActif(){
		$sql = "SELECT count(*) FROM worker WHERE termine=0";
		return $this->queryOne($sql);
	}
	
	public function getActif($offset=0,$limit=20){
		$offset = intval($offset);
		$limit = intval($limit);
		$sql = "SELECT * FROM worker".
				" LEFT JOIN job_queue ON job_queue.id_job = worker.id_job ".
				" LEFT JOIN job_queue_document ON job_queue_document.id_job = worker.id_job ".
				" WHERE termine=0 ".
				" ORDER BY date_begin". 
				" LIMIT $offset,$limit";
		return $this->query($sql);
	}
	
	public function getJobListWithWorker($offset=0,$limit=20){
		$sql = "SELECT *, job_queue.id_job as id_job FROM job_queue " .
				" JOIN job_queue_document ON job_queue.id_job=job_queue_document.id_job " .
				" LEFT JOIN worker ON job_queue.id_job = worker.id_job AND worker.termine = 0".
				" ORDER BY job_queue.id_job ".
				" LIMIT $offset,$limit " ;
		$result = $this->query($sql);
		foreach($result as $i => $line){
			$result[$i]['time_since_next_try'] = time() - strtotime($line['next_try']);
		}
		return $result;
	}
	
	
	
}