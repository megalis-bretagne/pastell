<?php


abstract class FluxData {

	abstract function getData($key);
	abstract function getFilename($key);
	abstract function getFileSHA256($key);
	abstract function getFilelist();
    abstract function setFileList($key, $filename, $filepath);
	abstract function getFilePath($key);
	abstract function getContentType($key);

}