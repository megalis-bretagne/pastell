<?php

require_once __DIR__."/../../../connecteur/seda-ng/lib/FluxDataStandard.class.php";

//TODO probablement a remonter dans la classe parente
class FluxDataSedaDefault extends FluxDataStandard {

    private $filenameCount = [];
    private $sha256Count = [];
    private $filePathCount = [];
    private $contentTypeCount = [];


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

    public function get_transfert_id(){
        return md5(time().mt_rand(0,mt_getrandmax()));
    }

    public function getFilename($key) {
        if (empty($this->filenameCount[$key])){
            $this->filenameCount[$key] = 0;
        }
        return $this->donneesFormulaire->getFileName($key,$this->filenameCount[$key]++);
    }

    public function getFileSHA256($key) {
        if (empty($this->sha256Count[$key])){
            $this->sha256Count[$key] = 0;
        }
        $file_path =  $this->donneesFormulaire->getFilePath($key,$this->sha256Count[$key]++);
        return hash_file("sha256",$file_path);
    }

    public function getFilePath($key){
        if (empty($this->filePathCount[$key])){
            $this->filePathCount[$key] = 0;
        }
        return $this->donneesFormulaire->getFilePath($key,$this->filePathCount[$key]++);
    }

    public function getContentType($key) {
        if (empty($this->contentTypeCount[$key])){
            $this->contentTypeCount[$key] = 0;
        }
        return $this->donneesFormulaire->getContentType($key,$this->contentTypeCount[$key]++);
    }
    
}