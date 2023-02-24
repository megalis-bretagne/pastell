<?php

class PastellTimer
{
    private $startingTime;

    public function __construct()
    {
        $this->startingTime = microtime(true);
    }

    public function getElapsedTime()
    {
        return microtime(true) - $this->startingTime;
    }
}
