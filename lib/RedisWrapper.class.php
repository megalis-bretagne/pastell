<?php


class RedisWrapper implements MemoryCache {

    private $host;
    private $port;

    private $redis;

    public function __construct($host = "localhost", $port = 6379) {
        $this->host = $host;
        $this->port = $port;
    }

    private function getRedis(){
        if (! $this->redis){
            $this->redis = new Redis();
            $this->redis->connect($this->host,$this->port);
        }
        return $this->redis;
    }

    public function store($id,$content,$time = 0){
        $this->getRedis()->set($id,serialize($content)); //TODO TTL
    }

    public function fetch($id){
        return unserialize($this->getRedis()->get($id));
    }

    public function delete($id){
        $this->getRedis()->del($id);
    }

}
