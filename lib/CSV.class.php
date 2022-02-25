<?php

class CSV
{
    public function get($file_path)
    {
        $file = $this->openFile($file_path);
        if (! $file) {
            return array();
        }

        $result = array();
        while (($data = fgetcsv($file, 1000, ";")) !== false) {
            $result[] = $data ;
        }
        fclose($file);
        return $result;
    }

    private function openFile($file_path)
    {
        if (! file_exists($file_path)) {
            return false;
        }
        $fileInfo = new finfo();
        $info = $fileInfo->file($file_path, FILEINFO_MIME_TYPE);
        echo $info;
        if (in_array($info, ['application/x-gzip', 'application/gzip'])) {
            return gzopen($file_path, "r");
        }
        return fopen($file_path, "r");
    }
}
