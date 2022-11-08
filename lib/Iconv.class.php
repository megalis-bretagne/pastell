<?php

class Iconv
{
    public const ISO_8850_15 = 'ISO-8859-15';
    public const UTF_8 = 'UTF-8';

    private Closure $loging_function;

    public function setLogingFunction(callable $loging_function)
    {
        $this->loging_function = $loging_function;
    }

    private function log($message)
    {
        if (isset($this->loging_function)) {
            call_user_func_array($this->loging_function, [$message]);
        }
    }

    public function convert($path, array $limit_extension = ['txt','php','yml'], $xcopy_before = false)
    {
        $path = rtrim($path, "/");
        if ($xcopy_before) {
            $this->xcopy($path, $path . ".old");
        }
        if (is_dir($path)) {
            $this->convertDirectoryAll($path, $limit_extension);
        } else {
            $this->convertFile($path, $limit_extension);
        }
    }

    private function xcopy($source, $destination)
    {
        $this->log("Copy $source to $destination");
        if (is_file($source)) {
            copy($source, $destination);
        } elseif (is_dir($source)) {
            mkdir($destination);
            $directory_handle = opendir($source);
            while ($file = readdir($directory_handle)) {
                if (in_array($file, ['.','..'])) {
                    continue;
                }
                $this->xcopy("$source/$file", "$destination/$file");
            }
        } else {
            throw new Exception("$source is not a file or a directory");
        }
    }


    private function convertFile($filepath, $limit_extensions)
    {
        if (! is_readable($filepath)) {
            throw new Exception("Impossible de lire le fichier $filepath");
        }
        if (! $this->hasExtension($filepath, $limit_extensions)) {
            $this->log("[$filepath] extension not in " . implode(",", $limit_extensions) . " : PASS");
            return;
        }
        $fileInfo = new finfo();
        $encoding = $fileInfo->file($filepath, FILEINFO_MIME_ENCODING);
        if (! in_array($encoding, ['iso-8859-15','iso-8859-1'])) {
            $this->log("[$filepath] encoding is $encoding : PASS");
            return;
        }
        $file_content = file_get_contents($filepath);
        $file_content = iconv(self::ISO_8850_15, self::UTF_8, $file_content);
        $this->log("[$filepath] Converting  to UTF-8 : OK");
        if ($file_content === false) {
            //I have not idea how to test that
            throw new Exception("Impossible de convertir le fichier $filepath...");
        }
        file_put_contents($filepath, $file_content);
    }

    private function hasExtension($file, array $extension_list)
    {

        foreach ($extension_list as $extension) {
            if (preg_match("#$extension$#", $file)) {
                return true;
            }
        }
        return false;
    }

    private function convertDirectoryAll($directory_path, array $limit_extensions)
    {
        $directory_handle = opendir($directory_path);
        while (false !== ($file = readdir($directory_handle))) {
            if (in_array($file, ['.','..'])) {
                continue;
            }
            $file_path = $directory_path . "/" . $file;

            if (is_dir($file_path)) {
                $this->log("Entering directory $file_path");
                $this->convertDirectoryAll($file_path, $limit_extensions);
                continue;
            }
            if (! $this->hasExtension($file_path, $limit_extensions)) {
                continue;
            }

            $this->convertFile($file_path, $limit_extensions);
        }
    }
}
