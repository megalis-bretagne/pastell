<?php

class SplitFileTest extends PHPUnit\Framework\TestCase
{
    /**
     * @throws Exception
     */
    public function testSplit()
    {
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        file_put_contents($tmp_folder . "/not-in-chunk", "foo");

        $logger = new  Monolog\Logger('PHPUNIT');
        $logger->pushHandler(new Monolog\Handler\NullHandler());
        $splitFile = new SplitFile($logger);

        copy(__DIR__ . "/fixtures/test.zip", $tmp_folder . "/test.zip");

        $chunk_list = $splitFile->split($tmp_folder . "/test.zip", 500, "chunk");

        $this->assertEquals(['chunkaa','chunkab'], $chunk_list);
        $this->assertFileExists($tmp_folder . "/chunkaa");
        $this->assertFileExists($tmp_folder . "/chunkab");
        $this->assertEquals(500, filesize($tmp_folder . "/chunkaa"));
        $this->assertEquals(315, filesize($tmp_folder . "/chunkab"));
    }
}
