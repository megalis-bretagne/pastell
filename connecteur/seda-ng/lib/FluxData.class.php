<?php


abstract class FluxData {

	abstract function getData($key);
	abstract function getFilename($key);
	abstract function getFileSHA256($key);
	abstract function getFilelist();
    abstract function setFileList($key, $filename, $filepath);
	abstract function getFilePath($key);
	abstract function getContentType($key);

	protected $connecteur_content;

	public function setConnecteurContent(array $connecteur_content){
		$this->connecteur_content = $connecteur_content;
	}

	/**
	 * @param string $clé du tableau connecteur_content
	 * @return string $valeur correspondante
	 * @throws UnrecoverableException Si la clé n'est pas trouvé, il y a un problème de configuration
	 */
	public function getConnecteurContent($index){
		if (! isset($this->connecteur_content[$index])){
			throw new UnrecoverableException("La clé $index n'est pas défini dans le connecteur SEDA NG");
		}
		return $this->connecteur_content[$index];
	}

}