<?php

class GEDLocal extends GED_NG_Connecteur {

    const GED_LOCAL_DIRECTORY = 'ged_local_directory';

    private $last_error;
    private $last_errno;

    public function listDirectory($directory_name){
        $directory_name = $this->sanitizeFilename($directory_name);
        return $this->callFileSystemFunction(
            function() use ($directory_name){
                return scandir($this->connecteurConfig->get(self::GED_LOCAL_DIRECTORY)."/".$directory_name);
            }
        );
    }

    public function makeDirectory($directory_name){
        $directory_name = $this->sanitizeFilename($directory_name);
        $directory_path = $this->connecteurConfig->get(self::GED_LOCAL_DIRECTORY)."/".$directory_name;
        $this->callFileSystemFunction(
            function() use ($directory_path){
                return mkdir($directory_path);
            }
        );
        return $directory_path;
    }

    public function saveDocument($directory_name, $filename, $filepath){
        $directory_name = $this->sanitizeFilename($directory_name);
        $filename = $this->sanitizeFilename($filename);
        $new_filepath = $this->connecteurConfig->get(self::GED_LOCAL_DIRECTORY)."/".$directory_name."/".$filename;
        $this->callFileSystemFunction(
            function() use ($filepath,$new_filepath){
                return copy($filepath, $new_filepath);
            }
        );
        return $new_filepath;
    }

    private function sanitizeFilename($filename){
        return strtr($filename,"/","_");
    }

    private function callFileSystemFunction(callable $function){
        set_error_handler (
            function($errno, $errstr) {
                $this->last_errno = $errno;
                $this->last_error = $errstr;
            }
        );
        $result = call_user_func($function);
        restore_error_handler();
        if ($result === false){
            throw new Exception("Erreur lors de l'accès au répertoire : " . $this->last_error);
        }
        return $result;
    }
}