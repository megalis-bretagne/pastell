<?php

class GEDLocal extends GED_NG_Connecteur {

    const GED_LOCAL_DIRECTORY = 'ged_local_directory';

    private $last_error;
    private $last_errno;

    public function listDirectory():array{
         return $this->callFileSystemFunction(
            function() {
                return scandir($this->connecteurConfig->get(self::GED_LOCAL_DIRECTORY));
            }
        );
    }

    public function makeDirectory(string $directory_name):string{
        $directory_name = $this->sanitizeFilename($directory_name);
        $directory_path = $this->connecteurConfig->get(self::GED_LOCAL_DIRECTORY)."/".$directory_name;
        $this->callFileSystemFunction(
            function() use ($directory_path){
                return mkdir($directory_path);
            }
        );
        return $directory_path;
    }

    public function saveDocument(string $directory_name, string $filename, string $filepath):string{
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

    public function directoryExists(string $directory_name):bool{
        $directory_path = $this->connecteurConfig->get(self::GED_LOCAL_DIRECTORY)."/".$directory_name;
        return  is_dir($directory_path) || file_exists($directory_path);
    }

    public function fileExists(string $file_name):bool{
        $directory_path = $this->connecteurConfig->get(self::GED_LOCAL_DIRECTORY)."/".$file_name;
        return file_exists($directory_path);
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