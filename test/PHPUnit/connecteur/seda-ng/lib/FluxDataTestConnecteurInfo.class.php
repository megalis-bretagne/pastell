<?php

class FluxDataTestConnecteurInfo extends FluxData
{
    private $flux_info;

    public function __construct()
    {
        $this->flux_info = [];
    }

    public function getData($key)
    {
        $method = "get_$key";
        if (method_exists($this, $method)) {
            return $this->$method($key);
        }
        if (isset($this->flux_info[$key])) {
            return $this->flux_info[$key];
        }
        return "";
    }

    public function get_id_producteur()
    {
        return $this->connecteur_content['id_producteur_hors_rh'];
    }


    public function getFileList()
    {
    }
    public function setFileList($key, $filename, $filepath)
    {
    }
    public function getFilename($key)
    {
    }
    public function getFileSHA256($key)
    {
    }
    public function getFilePath($key)
    {
    }
    public function getContentType($key)
    {
    }
    public function getFilesize($key)
    {
    }
    public function addZipToExtract($key)
    {
    }
}
