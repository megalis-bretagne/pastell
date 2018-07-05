<?php

require_once __DIR__."/../../../../connecteur/libersign/Libersign.class.php";

class LibersignTest extends \PHPUnit\Framework\TestCase {


	public function testInject(){
		$libersign = new Libersign();

		$xml = $libersign->injectSignaturePES(
			__DIR__."/fixtures/HELIOS_SIMU_ALR2_1529593767_1834011863.xml",
			file_get_contents(__DIR__."/fixtures/Signature.xml"),
			true
		);
		$this->assertStringEqualsFile(__DIR__."/fixtures/fichier_signe.xml",$xml);
	}

}