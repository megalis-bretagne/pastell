<?php
class DaemonManager {
	
	const IS_RUNNING = 1;
	const IS_STOPPED = 0;
	
	private $daemon_command;
	private $pid_file;
	private $log_file;
	private $user;
	
	public function __construct($daemon_command, $pid_file, $log_file,$user){
		$this->daemon_command = $daemon_command;
		$this->pid_file = $pid_file;
		$this->log_file = $log_file;
		$this->user = $user;
	}
	
	public function status(){
		$pid = $this->getDaemonPID();
		if (! $pid){
			return self::IS_STOPPED;
		}
		if (! posix_getpgid($pid)){
			return self::IS_STOPPED;
		}
		return self::IS_RUNNING;
	}
	
	public function getDaemonPID(){
		if (! is_readable($this->pid_file)){
			return false;
		}
		return intval(file_get_contents($this->pid_file));
	}
	
	public function start(){
		
		if ($this->status() == self::IS_RUNNING){
			return self::IS_RUNNING;
		}
		
		$this->createPIDFile();
		$this->createLogFile();
		
		$command = "nohup {$this->daemon_command} > {$this->log_file} 2>&1 & echo $! > {$this->pid_file} ";
		
		$user_info = posix_getpwuid(posix_getuid());
		if ($user_info['name'] != $this->user){
			echo "Starting daemon as {$this->user}\n";
			$command = "su - {$this->user} -c '$command'";
		}
		
		exec($command);
		return $this->status();
	}
	
	private function createPIDFile(){
		$this->initFile($this->pid_file);
	}
	
	private function createLogFile(){
		$this->initFile($this->log_file);
	}
	
	private function initFile($file){
		$err = @ file_put_contents($file, "");
		if ($err === false){
			throw new Exception("Impossible d'écrire le fichier {$file}");
		}
		$user_info = posix_getpwuid(posix_getuid());
		if ($user_info['name'] != $this->user){
			chown($file, $this->user);
		}
	}
	
	public function stop(){
		if ($this->status() == self::IS_STOPPED){
			return self::IS_STOPPED;
		}
		$pid = $this->getDaemonPID();
		posix_kill($pid,SIGTERM);
		return $this->status();
	}
	
	public function restart(){
		$this->stop();
		$this->start();
	}
}