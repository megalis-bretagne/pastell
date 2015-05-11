<?php
require_once __DIR__.'/../init.php';

class ManifestReaderTest extends PHPUnit_Framework_TestCase {
	
	private function getManifestReader(){
		$manifestFactory = new ManifestFactory("toto");
		
		$manifestReader = $manifestFactory->getManifest(__DIR__."/../fixtures/extensions/extension-test/");
		return $manifestReader;
	}
	
	private function getExtensionManifestReader(){
		$manifestFactory = new ManifestFactory("toto");
		$manifestReader = $manifestFactory->getManifest(__DIR__."/../fixtures/extensions/extension-test2/");		
		return $manifestReader;
	}
	
	public function testGetInfo(){
		$info = $this->getManifestReader()->getInfo();
		$this->assertInternalType('array', $info);
		$this->assertArrayHasKey('nom', $info);
		$this->assertEquals('Glaneur', $info['nom']);
	}
	
	public function testGetRevision(){
		$revision = $this->getManifestReader()->getRevision();
		$this->assertEquals('9', $revision);
	}
	
	public function testGetVersion(){
		$version = $this->getManifestReader()->getVersion();
		$this->assertEquals('4', $version);
	}
	
	public function testRevisionOk(){
		$this->assertTrue($this->getManifestReader()->isVersionOK('3'));
	}
	
	public function testRevisionFailed(){
		$this->assertFalse($this->getManifestReader()->isVersionOK('12'));
	}
	
	public function testGetExtensionNeededFalse(){
		$this->assertFalse($this->getExtensionManifestReader()->getExtensionNeeded());
	}
	
	public function testGetExtensionNeeded(){
		$manifestReader = $this->getManifestReader();
		$extension_needed = $manifestReader->getExtensionNeeded();
		$this->assertInternalType('array', $extension_needed);
		$this->assertArrayHasKey('pastell-mnesys', $extension_needed);
		$this->assertArrayHasKey('version', $extension_needed['pastell-mnesys']);
		$this->assertEquals(2, $extension_needed['pastell-mnesys']['version']);
	}
	
	public function testManifestEmpy(){
		$manifestFactory = new ManifestFactory("toto");
		$this->setExpectedException('PHPUnit_Framework_Error_Warning');
		$manifestReader = $manifestFactory->getManifest(__DIR__."/../fixtures/extensions/extension-test3/");
		$this->assertFalse($manifestReader->isVersionOK(12));
	}
	
	
	
} 