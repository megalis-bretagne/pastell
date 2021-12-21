<?php

class FluxDataTest extends FluxData
{
    private $flux_info;
    private $file_list;

    public function __construct(array $flux_info)
    {
        $this->flux_info = $flux_info;
        $this->file_list = array();
    }

    public function getData($key)
    {
        if (isset($this->flux_info[$key])) {
            return $this->flux_info[$key];
        }
        return "";
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

    public function getFilename($key)
    {
        return $this->getData($key);
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
