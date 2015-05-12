<?php
require_once __DIR__.'/../init.php';

class ManifestReaderTest extends PHPUnit_Framework_TestCase {
	
	private function getManifestReader(){
		$manifest = array(	'nom'=>'Glaneur',
							'revision'=>'$Rev: 9 $',
							'version'=>4,
							'extensions_versions_accepted'=>array(2,3),
							'extension_needed' => array('pastell-megalis'=>array('version'=>1),'pastell-mnesys'=>array('version'=>2))
		);
		$manifestReader = new ManifestReader($manifest);
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
	
	public function testGetExtensionNeeded(){
		$manifestReader = $this->getManifestReader();
		$extension_needed = $manifestReader->getExtensionNeeded();
		$this->assertInternalType('array', $extension_needed);
		$this->assertArrayHasKey('pastell-mnesys', $extension_needed);
		$this->assertArrayHasKey('version', $extension_needed['pastell-mnesys']);
		$this->assertEquals(2, $extension_needed['pastell-mnesys']['version']);
	}
	
	public function testManifestEmpy(){
		$manifestReader = new ManifestReader(array());
		$this->assertFalse($manifestReader->isVersionOK(12));
	}

} 