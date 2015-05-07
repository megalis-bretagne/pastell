<?php
require_once __DIR__.'/../init.php';

class ManifestReaderTest extends PHPUnit_Framework_TestCase {
	
	private function getManifestReader(){
		$manifestReader = new ManifestReader(new YMLLoader(), __DIR__."/../fixtures/manifest.yml");
		return $manifestReader;
	}
	
	private function getExtensionManifestReader(){
		$manifestReader = new ManifestReader(new YMLLoader(), __DIR__."/../fixtures/manifest-extension.yml");
		return $manifestReader;
	}
	
	public function testGetInfo(){
		$info = $this->getManifestReader()->getInfo();
		$this->assertInternalType('array', $info);
		$this->assertArrayHasKey('nom', $info);
		$this->assertEquals('Pastell coeur', $info['nom']);
	}
	
	public function testGetRevision(){
		$revision = $this->getManifestReader()->getRevision();
		$this->assertEquals('679', $revision);
	}
	
	public function testGetVersion(){
		$version = $this->getManifestReader()->getVersion();
		$this->assertEquals('1.1.4', $version);
	}
	
	public function testRevisionOk(){
		$this->assertTrue($this->getManifestReader()->isRevisionOK('1.1.3'));
	}
	
	public function testRevisionFailed(){
		$this->assertFalse($this->getManifestReader()->isRevisionOK('12'));
	}
	
	public function testGetExtensionNeededFalse(){
		$this->assertFalse($this->getManifestReader()->getExtensionNeeded());
	}
	
	public function testGetExtensionNeeded(){
		$manifestReader = $this->getExtensionManifestReader();
		$extension_needed = $manifestReader->getExtensionNeeded();
		$this->assertInternalType('array', $extension_needed);
		$this->assertArrayHasKey('pastell-mnesys', $extension_needed);
		$this->assertArrayHasKey('version', $extension_needed['pastell-mnesys']);
		$this->assertEquals(2, $extension_needed['pastell-mnesys']['version']);
	}
	
	public function testManifestEmpy(){
		$manifestReader = new ManifestReader(new YMLLoader(), __DIR__."/../fixtures/manifest-empty.yml");
		$this->assertFalse($manifestReader->isRevisionOK(12));
	}
	
	
	
} 