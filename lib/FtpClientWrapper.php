<?php

// Cette classe permet d'attaper les warnings et de les transformer en exceptions

class FtpClientWrapper extends \FtpClient\FtpClient
{
    private $last_error;
    private $last_errno;


    public function __call($method, array $arguments)
    {
        return $this->callFileSystemFunction(function () use ($method, $arguments) {
            return  parent::__call($method, $arguments);
        });
    }

    public function mkdir($directory, $recursive = false)
    {
        return $this->callFileSystemFunction(function () use ($directory, $recursive) {
            return parent::mkdir($directory, $recursive);
        });
    }

    public function login($username = 'anonymous', $password = '')
    {
        return $this->callFileSystemFunction(function () use ($username, $password) {
            return parent::login($username, $password);
        });
    }

    public function nlist($directory = '.', $recursive = false, $filter = 'sort')
    {
        return $this->callFileSystemFunction(function () use ($directory, $recursive, $filter) {
            return parent::nlist($directory, $recursive, $filter);
        });
    }

    private function callFileSystemFunction(callable $function)
    {
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->last_errno = $errno;
                $this->last_error = $errstr;
                return true;
            }
        );
        $result = call_user_func($function);
        restore_error_handler();
        if ($result === false) {
            throw new Exception(sprintf("Erreur FTP #%d: %s", $this->last_errno, $this->last_error));
        }
        return $result;
    }
}
