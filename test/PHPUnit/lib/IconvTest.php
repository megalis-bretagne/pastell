<?php

use PHPUnit\Framework\TestCase;

class IconvTest extends TestCase
{
    private $file_in_iso;
    private $file_in_utf8;

    /** @var  Iconv */
    private $iconv;

    private $streamURL;
    private $temporary_file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file_in_iso = __DIR__ . '/fixtures/file_in_iso-8859-15.txt';
        $this->file_in_utf8 = __DIR__ . '/fixtures/file_in_utf8.txt';

        $this->iconv = new Iconv();

        org\bovigo\vfs\vfsStream::setup('IconvTest', null, array());
        $this->streamURL = org\bovigo\vfs\vfsStream::url('IconvTest');
        $this->temporary_file = $this->streamURL . '/temporary_file.txt';
    }

    public function testConvertFile()
    {
        copy($this->file_in_iso, $this->temporary_file);
        $this->iconv->convert($this->temporary_file);
        $this->assertFileEquals($this->file_in_utf8, $this->temporary_file);
    }

    public function testConvertFileAlreadyInUTF8()
    {
        copy($this->file_in_utf8, $this->temporary_file);
        $this->iconv->convert($this->temporary_file);
        $this->assertFileEquals($this->file_in_utf8, $this->temporary_file);
    }

    public function testFileDoesNotExists()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Impossible de lire le fichier vfs://IconvTest/temporary_file.txt");
        $this->iconv->convert($this->temporary_file, array('txt'));
    }

    private function createDirectory()
    {
        $temporary_dir = $this->streamURL . "/directory/";
        mkdir($temporary_dir);
        $file1 = $temporary_dir . "/test1.txt";
        $file2 = $temporary_dir . "/test2.txt";

        copy($this->file_in_iso, $file1);
        copy($this->file_in_utf8, $file2);
        return $temporary_dir;
    }

    public function testConvertDirectory()
    {
        $temporary_dir = rtrim($this->createDirectory(), "/");
        $this->iconv->convert($temporary_dir, array("txt"), true);
        $this->assertFileEquals($this->file_in_utf8, $temporary_dir . "/test1.txt");
        $this->assertFileEquals($this->file_in_utf8, $temporary_dir . "/test2.txt");
        $this->assertFileEquals($this->file_in_iso, $temporary_dir . ".old/test1.txt");
        $this->assertFileEquals($this->file_in_utf8, $temporary_dir . ".old/test2.txt");
    }

    public function testExcludeFromDirectory()
    {
        copy($this->file_in_iso, $this->temporary_file);
        $this->iconv->convert($this->temporary_file, array("php"));
        $this->assertFileEquals($this->file_in_iso, $this->temporary_file);
    }

    public function testConvertDirectoryRecursive()
    {
        $temporary_dir = $this->createDirectory();
        mkdir($temporary_dir . "/subdir");
        $file3 = $temporary_dir . "/subdir/test3.txt";
        copy($this->file_in_iso, $file3);
        $this->iconv->convert($temporary_dir, array("txt"));
        $this->assertFileEquals($this->file_in_utf8, $file3);
    }

    public function testConvertDirectoryAll()
    {
        $temporary_dir = $this->createDirectory();
        $this->iconv->convert($temporary_dir, array("txt"));
        $this->assertFileEquals($this->file_in_utf8, $temporary_dir . "/test1.txt");
        $this->assertFileEquals($this->file_in_utf8, $temporary_dir . "/test2.txt");
    }

    public function testLogging()
    {
        $this->expectOutputString("[vfs://IconvTest/temporary_file.txt] Converting  to UTF-8 : OK");
        $this->iconv->setLogingFunction(function ($message) {
            echo $message;
        });
        copy($this->file_in_iso, $this->temporary_file);
        $this->iconv->convert($this->temporary_file);
    }
}
