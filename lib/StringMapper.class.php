<?php

class StringMapper
{
    private $strings_map = [];

    public function setMapping(array $strings_map)
    {
        $this->strings_map = $strings_map;
    }

    public function get(string $string): string
    {
        return $this->strings_map[$string] ?? $string;
    }

    public function map(string &$string): void
    {
        if (isset($this->strings_map[$string])) {
            $string = $this->strings_map[$string];
        }
    }

    public function add($original_value, $mapped_value)
    {
        $this->strings_map[$original_value] = $mapped_value;
    }

    public function getAll()
    {
        return $this->strings_map;
    }
}
