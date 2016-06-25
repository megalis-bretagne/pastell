<?php


class ExtensionControllerTest extends PastellTestCase {

	public function testList(){

		/** @var  BaseAPIControllerFactory $factory */
		$factory = $this->getObjectInstancier()->getInstance('BaseAPIControllerFactory');

		/** @var ExtensionController $extensionController */
		$extensionController = $factory->getInstance('Extension',1);

		$list = $extensionController->listAction();
		$this->assertEquals('/var/lib/pastell/pastell_cdg59',$list['result'][1]['path']);
		
	}

}