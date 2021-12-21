<?php

class RedisWrapper implements MemoryCache
{
    private $host;
    private $port;

    private $redis;

    public function __construct($host = "localhost", $port = 6379)
    {
        $this->host = $host;
        $this->port = $port;
    }

    private function getRedis()
    {
        if (! $this->redis) {
            $this->redis = new Redis();
            $this->redis->connect($this->host, $this->port);
        }
        return $this->redis;
    }

    public function store($id, $content, $ttl = 0)
    {
        try {
            /* https://github.com/phpredis/phpredis/issues/732 */
            if ($ttl) {
                $this->getRedis()->set($id, serialize($content), $ttl);
            } else {
                $this->getRedis()->set($id, serialize($content));
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function fetch($id)
    {
        try {
            return unserialize($this->getRedis()->get($id));
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $this->getRedis()->del($id);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function flushAll()
    {
        $this->getRedis()->flushAll();
    }

    public function getNumberOfKeys(): int
    {
        return count($this->getRedis()->keys("*"));
    }
}
