<?php

class MemoryCacheNone implements MemoryCache
{
    public function store($id, $content, $time = 0)
    {
    }

    public function fetch($id)
    {
        return false;
    }

    public function delete($id)
    {
    }

    public function flushAll()
    {
    }
}
