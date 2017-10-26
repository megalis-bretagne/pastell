<?php

class SimpleXMLWrapperTest extends LegacyPHPUnit_Framework_TestCase {

	/**
	 * @var SimpleXMLWrapper
	 */
	private $simpleXMLWrapper;

	public function setUp(){
		$this->simpleXMLWrapper = new SimpleXMLWrapper();
	}

	public function testLoadBadString(){
		$this->setExpectedException("Exception","XML incorrect");
		$this->simpleXMLWrapper->loadString("foo");
	}

	public function testLoadString(){
		$this->assertEquals("foo",$this->simpleXMLWrapper->loadString("<foo></foo>")->getName());
	}

	public function testLoadFile(){
		$file_path = $this->getFilePath("<foo></foo>");
		$xml = $this->simpleXMLWrapper->loadFile($file_path);
		$this->assertEquals("foo",$xml->getName());
	}

	public function testLoadBadFile(){
		$file_path = $this->getFilePath("foo");
		$this->setExpectedException("Exception","Le fichier vfs://test/fichier.xml n'est pas un XML correct");
		$this->simpleXMLWrapper->loadFile($file_path);
	}

	private function getFilePath($file_content){
		org\bovigo\vfs\vfsStream::setup('test');
		$testStreamUrl = org\bovigo\vfs\vfsStream::url('test');
		$file_path = $testStreamUrl."/fichier.xml";
		file_put_contents($file_path,$file_content);
		return $file_path;
	}


}