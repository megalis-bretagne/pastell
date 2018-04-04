<?php
class FileUploaderMock extends FileUploader {

	private $files;



	public function setFiles($files){
		$this->files = $files;
	}

	public function getFilePath($filename){
		throw new Exception("Not implemented");
	}

	public function getName($filename){
		return $filename;
	}

	public function getLastError(){
		throw new Exception("Not implemented");
	}

	public function getFileContent($form_name){
		return $this->files[$form_name];
	}

	public function save($filename,$save_path){
		throw new Exception("Not implemented");
	}

	public function getAll(){
		return $this->files;
	}
}