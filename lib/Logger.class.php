<?php

class Logger {

    private $log_file;

    public function __construct($log_file) {
        $this->log_file = $log_file;
    }

    public function log($message){
        $message = date("Y-m-d H:i:s")." $message\n";
        file_put_contents($this->log_file,$message,FILE_APPEND|LOCK_EX);
    }
}