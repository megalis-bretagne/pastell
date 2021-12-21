<?php

class FileUploaderTest extends PHPUnit\Framework\TestCase
{
    public const FILE_CONTENT = "Hello World!";
    private $tmp_file;

    protected function setUp()
    {
        parent::setUp();
        org\bovigo\vfs\vfsStream::setup('test');
        $testStreamUrl = org\bovigo\vfs\vfsStream::url('test');
        $this->tmp_file = $testStreamUrl . "/test.text";
        file_put_contents($this->tmp_file, self::FILE_CONTENT);
    }


    private function getFileUploader($_files)
    {
        $fileUploader = new FileUploader();
        $fileUploader->setFiles($_files);
        return $fileUploader;
    }


    public function testGetFailed()
    {
        $fileUploader = $this->getFileUploader([]);
        $this->assertFalse($fileUploader->getName('foo'));
        $this->assertEquals("Fichier foo inexistant", $fileUploader->getLastError());
        $this->assertFalse($fileUploader->getFilePath('foo'));
        $this->assertFalse($fileUploader->getFileContent('foo'));
        $this->assertFalse($fileUploader->getNbFile('foo'));
    }

    /**
     * @throws Exception
     */
    public function testGetOneFile()
    {
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        $tmp_file = $tmp_folder . "/var.txt";
        file_put_contents($tmp_file, self::FILE_CONTENT);

        $fileUploader = $this->getFileUploader(array("foo" =>
            array(
                'name' => 'bar',
                'type' => 'text/plain',
                'size' => 42,
                'tmp_name' => $tmp_file,
                'error' => UPLOAD_ERR_OK)
        ));
        $this->assertEquals(1, $fileUploader->getNbFile('foo'));
        $this->assertEquals('bar', $fileUploader->getName('foo'));
        $this->assertEquals($tmp_file, $fileUploader->getFilePath('foo'));
        $this->assertEquals(self::FILE_CONTENT, $fileUploader->getFileContent('foo'));
        $this->assertEquals(['foo' => 'bar'], $fileUploader->getAll());
        $fileUploader->save("foo", "$tmp_folder/test.txt");
        $this->assertEquals(self::FILE_CONTENT, file_get_contents($tmp_folder . "/test.txt"));
        $tmpFolder->delete($tmp_folder);
    }

    public function testGetManyFiles()
    {
        $fileUploader = $this->getFileUploader(
            ["foo" =>
            array(
                'name' => ['bar','baz'],
                'type' => ['text/plain','application/xml'],
                'size' => [42,43],
                'tmp_name' => [$this->tmp_file,$this->tmp_file],
                'error' => [UPLOAD_ERR_OK,UPLOAD_ERR_OK])
            ]
        );
        $this->assertEquals(2, $fileUploader->getNbFile('foo'));
        $this->assertEquals('bar', $fileUploader->getName('foo', 0));
        $this->assertEquals('baz', $fileUploader->getName('foo', 1));
        $this->assertFalse($fileUploader->getName('foo', 12));
    }

    public function testGetOneFileError()
    {

        $fileUploader = $this->getFileUploader(array("foo" =>
            array(
                'name' => 'bar',
                'type' => 'text/plain',
                'size' => 42,
                'tmp_name' => $this->tmp_file,
                'error' => UPLOAD_ERR_FORM_SIZE)
        ));
        $this->assertEquals(1, $fileUploader->getNbFile('foo'));
        $this->assertFalse($fileUploader->getName('foo'));
    }

    public function testGetManyFilesError()
    {
        $fileUploader = $this->getFileUploader(
            ["foo" =>
                array(
                    'name' => ['bar','baz'],
                    'type' => ['text/plain','application/xml'],
                    'size' => [42,43],
                    'tmp_name' => [$this->tmp_file,$this->tmp_file],
                    'error' => [UPLOAD_ERR_OK,UPLOAD_ERR_FORM_SIZE])
            ]
        );
        $this->assertEquals(2, $fileUploader->getNbFile('foo'));
        $this->assertEquals('bar', $fileUploader->getName('foo', 0));
        $this->assertFalse($fileUploader->getName('foo', 1));
        $this->assertEquals("Le fichier dépasse la taille limite autorisé par le formulaire", $fileUploader->getLastError());
    }


    public function testGetOneFileUnknowError()
    {

        $fileUploader = $this->getFileUploader(array("foo" =>
            array(
                'name' => 'bar',
                'type' => 'text/plain',
                'size' => 42,
                'tmp_name' => $this->tmp_file,
                'error' => 142)
        ));
        $this->assertEquals(1, $fileUploader->getNbFile('foo'));
        $this->assertFalse($fileUploader->getName('foo'));
        $this->assertEquals("Erreur inconnue (142) lors de l'upload du fichier", $fileUploader->getLastError());
    }
}
