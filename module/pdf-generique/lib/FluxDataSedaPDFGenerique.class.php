<?php


class FluxDataSedaPDFGenerique extends FluxDataStandard {

	private $metadata;

	public function setMetadata(array $metadata){
		$this->metadata = $metadata;
	}

	public function getData($key) {

		if (isset($this->metadata[$key])){
			return $this->metadata[$key];
		}

        $method = "get_$key";
        if (method_exists($this, $method)){
            return $this->$method($key);
        }

		return parent::getData($key);
	}


    public function getFilename($key) {
        $method = "getFilename_$key";
        if (method_exists($this, $method)){
            return $this->$method($key);
        }
        return parent::getFilename($key);
    }

    public function getFilepath($key) {
        $method = "getFilepath_$key";
        if (method_exists($this, $method)){
            return $this->$method($key);
        }
        return parent::getFilepath($key);
    }

    public function getContentType($key) {
        $method = "getContentType_$key";
        if (method_exists($this, $method)){
            return $this->$method($key);
        }
        return parent::getContentType($key);
    }

    public function getFileSHA256($key) {
        $method = "getFilesha256_$key";
        if (method_exists($this, $method)){
            return $this->$method($key);
        }
        return parent::getFileSHA256($key);
    }

    public function get_journal_size_in_bytes(){
        return filesize($this->donneesFormulaire->getFilePath('journal'));
    }

    public function get_document_size_in_bytes(){
        return filesize($this->donneesFormulaire->getFilePath('document'));
    }

    public function get_annexe(){

        $annexe = $this->donneesFormulaire->get('annexe');
        return $annexe;
    }

    public function get_annexe_size_in_bytes(){

        $result = array();

        foreach ($this->donneesFormulaire->get('annexe') as $i => $title){
            $result[] = filesize($this->donneesFormulaire->getFilePath('annexe',$i));
        }
        return $result;

    }
    public function getContentType_annexe(){
        static $i = 0;
        $content_type = $this->donneesFormulaire->getContentType('annexe',$i++);
        return $content_type;
    }

    public function getFilepath_annexe(){
        static $i = 0;
        return $this->donneesFormulaire->getFilePath('annexe',$i++);
    }

    public function getFilename_annexe(){
        static $i = 0;
        return $this->donneesFormulaire->getFileName('annexe',$i++);
    }

    public function getFilesha256_annexe(){
        static $i = 0;
        return hash_file("sha256",$this->donneesFormulaire->getFilePath('annexe',$i++));
    }


}