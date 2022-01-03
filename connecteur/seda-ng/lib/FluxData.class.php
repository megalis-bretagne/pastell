<?php

abstract class FluxData
{
    abstract public function getData($key);
    abstract public function getFilename($key);
    abstract public function getFileSHA256($key);
    abstract public function getFilelist();
    abstract public function setFileList($key, $filename, $filepath);
    abstract public function getFilePath($key);
    abstract public function getContentType($key);
    abstract public function getFilesize($key);
    abstract public function addZipToExtract($key);


    protected $connecteur_content;

    public function setConnecteurContent(array $connecteur_content)
    {
        $this->connecteur_content = $connecteur_content;
    }

    /**
     * @param string $clé du tableau connecteur_content
     * @return string $valeur correspondante
     */
    public function getConnecteurContent($index)
    {
        if (! isset($this->connecteur_content[$index])) {
            return "";
        }
        return $this->connecteur_content[$index];
    }
}
