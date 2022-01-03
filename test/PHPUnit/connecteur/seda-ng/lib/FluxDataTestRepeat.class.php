<?php

class FluxDataTestRepeat extends FluxData
{
    private $flux_info;
    private $file_list;

    public function __construct()
    {
        $this->flux_info = [];
        $this->file_list = array();
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

    private $num_dossier = 0;

    public function get_fichier()
    {
        $file_list = [
            ['pim','pam'],
            ['toto'],
            ['foo','bar','baz']
        ];

        $result = $file_list[$this->num_dossier++];
        return $result;
    }


    public function get_folder()
    {
        return array('foo','Bar','Baz');
    }

    public function getFileList()
    {
        return $this->file_list;
    }

    public function setFileList($key, $filename, $filepath)
    {
        $this->file_list[] = array(
            'key' => $key,
            'filename' => $filename,
            'filepath' => $filepath);
    }

    private $num_fichier = 0;
    public function getFilename($key)
    {
        $file_list = [
            'pim','pam',
            'toto',
            'foo','bar','baz'
        ];
        return $file_list[$this->num_fichier++];
    }

    public function getFileSHA256($key)
    {
        return hash('sha256', base64_encode(mt_rand(0, mt_getrandmax())));
    }

    public function getFilePath($key)
    {
        return "/dev/null";
    }

    public function getContentType($key)
    {
        return "";
    }

    public function getFilesize($key)
    {
        return "42";
    }
    public function addZipToExtract($key)
    {
    }
}
