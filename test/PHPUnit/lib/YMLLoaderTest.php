<?php

class YMLLoaderTest extends PHPUnit\Framework\TestCase {

    /** @var  YMLLoader */
    private $ymlLoader;

    /** @var  StaticWrapper */
    private $staticWrapper;

    protected function setUp(){
        $this->staticWrapper = new StaticWrapper();
        $this->ymlLoader = new YMLLoader($this->staticWrapper);
    }

    public function testGetArray(){
        $result = $this->ymlLoader->getArray(__DIR__."/fixtures/test.yml");
        $this->assertInternalType('array',$result);
        $this->assertEquals('chat',$result['animaux']['mamifère']['félin'][0]);
    }

    public function testGetArrayWithCache(){
        $filename = __DIR__."/fixtures/test.yml";
        $mtime_orig = filemtime($filename);
        $result = $this->ymlLoader->getArray($filename);
        $this->assertEquals('chat',$result['animaux']['mamifère']['félin'][0]);

        $result_from_cache = $this->staticWrapper->fetch(YMLLoader::CACHE_PREFIX.$filename);
        $this->assertEquals('chat',$result_from_cache['animaux']['mamifère']['félin'][0]);

        $this->assertEquals($mtime_orig,$this->staticWrapper->fetch(YMLLoader::CACHE_PREFIX_MTIME.$filename));

        $result = $this->ymlLoader->getArray(__DIR__."/fixtures/test.yml");
        $this->assertEquals('chat',$result['animaux']['mamifère']['félin'][0]);
    }

    public function testSaveArray(){
        $filename = "/tmp/".uniqid("yml_loader_test_");
        $this->ymlLoader->saveArray($filename,array("foo"=>"bar"));
        $result = file_get_contents($filename);
        $this->assertEquals("---\nfoo: bar\n",$result);
        unlink($filename);
    }

    public function testSaveArrayInvalidateCache(){
        $array = array("foo"=>"bar");
        $filename = "/tmp/".uniqid("yml_loader_test_");
        $this->ymlLoader->saveArray($filename,$array);
        $this->assertEquals($array,$this->ymlLoader->getArray($filename));
        $this->assertEquals($array,$this->staticWrapper->fetch(YMLLoader::CACHE_PREFIX.$filename));
        $array = array("foo2"=>"baz");
        $this->ymlLoader->saveArray($filename,$array);
        $this->assertEquals($array,$this->ymlLoader->getArray($filename));
        $this->assertEquals($array,$this->staticWrapper->fetch(YMLLoader::CACHE_PREFIX.$filename));
        unlink($filename);
    }

    public function testFileNotFound(){
        $ymlLoader = new YMLLoader(new MemoryCacheNone());
        $this->assertFalse($ymlLoader->getArray("file does not exists"));
    }


}