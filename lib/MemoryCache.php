<?php

interface MemoryCache
{
    public function store($id, $content, $time = 0);
    public function fetch($id);
    public function delete($id);
    public function flushAll();
}
