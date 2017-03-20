<?php


class VersionAPIControllerTest extends PastellTestCase {

	public function testGet(){
		/** @var VersionAPIController $versionController */
		$versionController = $this->getAPIController('version',1);
		$info = $versionController->get();
		$this->assertEquals('1.4-fixtures',$info['version']);
	}




}