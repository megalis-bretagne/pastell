<?php
class RecuperationFichierSSH extends RecuperationFichier {

    /** @var  DonneesFormulaire */
	private $donneesFormulaire;
	
	private $ssh2;
	
	public function __construct(SSH2 $ssh2){
		$this->ssh2 = $ssh2;
	}
	
	public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
		$this->donneesFormulaire = $donneesFormulaire;
	}
	
	public function getProperties($name){
		return $this->donneesFormulaire->get($name);
	}
	
	private function getFilePath($name){
		return $this->donneesFormulaire->getFilePath($name);
	}
	
	private function configSSH2(){
		$this->ssh2->setServerName($this->getProperties('ssh_server'),$this->getProperties('ssh_fingerprint'),$this->getProperties('ssh_port'));
		$this->ssh2->setPubKeyAuthentication($this->getFilePath('ssh_public_key'), $this->getFilePath('ssh_private_key'), $this->getProperties('ssh_private_password'));
		$this->ssh2->setPasswordAuthentication($this->getProperties('ssh_login'),$this->getProperties('ssh_password'));
	}
	
	public function listFile() {
		$this->configSSH2();
		$directory_listing = $this->ssh2->listDirectory($this->getProperties("ssh_directory"));
		if (!$directory_listing){
			throw new Exception($this->ssh2->getLastError());
		}
		return $directory_listing;
	}

	public function listFile_send() {
		$this->configSSH2();
		$directory_listing = $this->ssh2->listDirectory($this->getProperties("ssh_directory_send"));
		if (!$directory_listing){
			throw new Exception($this->ssh2->getLastError());
		}
		return $directory_listing;
	}

	public function retrieveFile($filename, $destination_directory){
		$this->configSSH2();
		if (! $this->ssh2->retrieveFile($this->getProperties("ssh_directory")."/".$filename,$destination_directory."/".$filename)){
			throw new Exception($this->ssh2->getLastError());
		}
		return true;
	}
	
	public function deleteFile($filename){
		$this->configSSH2();
		if (! $this->ssh2->deleteFile($this->getProperties("ssh_directory")."/".$filename)) {
			throw new UnrecoverableException($this->ssh2->getLastError());
		}
		return true;
	}
	
	public function sendFile($source_directory, $filename){
		$this->configSSH2();
		if ($this->getProperties("ssh_directory_send")) {
			if (! $this->ssh2->sendFile($source_directory."/".$filename,$this->getProperties("ssh_directory_send")."/".$filename)) {
				throw new UnrecoverableException($this->ssh2->getLastError());
			}
		}
		return true;
	}	
}