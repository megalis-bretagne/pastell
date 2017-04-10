<?php


class FamilleConnecteurAPIController extends BaseAPIController {

	private $connecteurDefinitionFiles;

	public function __construct(ConnecteurDefinitionFiles $connecteurDefinitionFiles) {
		$this->connecteurDefinitionFiles = $connecteurDefinitionFiles;
	}

	public function get(){
		$this->checkDroit(0,"system:lecture");
		$global = $this->getFromRequest('global');
		if ($global){
			return $this->connecteurDefinitionFiles->getAllGlobalType();
		}
		return $this->connecteurDefinitionFiles->getAllType();
	}

}