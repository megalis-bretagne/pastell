<?php

require_once(__DIR__."/FluxData.class.php");

class FluxDataStandard extends FluxData {

	protected $donneesFormulaire;
	protected $file_list;

	public function __construct(DonneesFormulaire $donneesFormulaire) {
		$this->donneesFormulaire = $donneesFormulaire;
		$this->file_list = array();
	}

	public function getData($key) {
		return $this->donneesFormulaire->get($key);
	}

	public function getFileList() {
		return $this->file_list;
	}

    public function setFileList($key, $filename, $filepath) {
        $this->file_list[] = array(
            'key' => $key,
            'filename' => $filename,
            'filepath' => $filepath);

    }

    public function getFilename($key) {
		return $this->donneesFormulaire->getFileName($key);
	}

	public function getFileSHA256($key) {
		$file_path =  $this->donneesFormulaire->getFilePath($key);
		return hash_file("sha256",$file_path);
	}

	public function getFilePath($key){
		return $this->donneesFormulaire->getFilePath($key);
	}

	public function getContentType($key) {
		return $this->donneesFormulaire->getContentType($key);
	}


}