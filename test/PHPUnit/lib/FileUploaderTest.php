<?php

class FileUploaderTest extends PHPUnit_Framework_TestCase {

	public function testGetNameFailed(){
		$fileUploader = new FileUploader();
		$this->assertFalse($fileUploader->getName('foo'));
		$this->assertEquals("Aucun fichier reçu (code : )",$fileUploader->getLastError());
	}

	public function testGetName(){
		$fileUploader = new FileUploader();

		org\bovigo\vfs\vfsStream::setup('test');
		$testStreamUrl = org\bovigo\vfs\vfsStream::url('test');
		$tmp_file = $testStreamUrl."/test.text";

		file_put_contents($tmp_file,"Hello World!");

		$file = array("foo" =>
			array(
				'name'=>'bar',
				'type'=>'text/plain',
				'size'=>42,
				'tmp_name'=>$tmp_file,
				'error'=>UPLOAD_ERR_OK)
		);

		$fileUploader->setFiles($file);

		$this->assertEquals('bar',$fileUploader->getName('foo'));

	}


}