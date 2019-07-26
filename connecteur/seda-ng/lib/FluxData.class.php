<?php


abstract class FluxData {

	abstract function getData($key);
	abstract function getFilename($key);
	abstract function getFileSHA256($key);
	abstract function getFilelist();
    abstract function setFileList($key, $filename, $filepath);
	abstract function getFilePath($key);
	abstract function getContentType($key);
	abstract function getFilesize($key);


	protected $connecteur_content;

	public function setConnecteurContent(array $connecteur_content){
		$this->connecteur_content = $connecteur_content;
	}

	/**
	 * @param string $clé du tableau connecteur_content
	 * @return string $valeur correspondante
	 */
	public function getConnecteurContent($index){
		if (! isset($this->connecteur_content[$index])){
			return "";
		}
		return $this->connecteur_content[$index];
	}

}