<?php

class EnvWrapper
{
    private $env;

    public function __construct(array $env = array())
    {
        if (! $env) {
            $env = $_ENV;
        }
        $this->env = $env;
    }

    public function get($key, $default)
    {
        if (isset($this->env[$key])) {
            return $this->env[$key];
        }
        return $default;
    }
}
