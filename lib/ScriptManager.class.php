<?php

class ScriptManager
{
    private $argv;

    private $mandatory_arg = array();

    public function setMandatoryArg($option, $description)
    {
        $this->mandatory_arg[$option] = $description;
    }

    public function setArgument($argv)
    {
        $this->argv = $argv;
    }
}
