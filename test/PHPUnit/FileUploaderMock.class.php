<?php

class FileUploaderMock extends FileUploader
{
    private $files;

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function getFilePath($filename, $num_file = 0)
    {
        throw new Exception("Not implemented");
    }

    public function getName($filename, $num_file = 0)
    {
        return $filename;
    }

    public function getLastError()
    {
        throw new Exception("Not implemented");
    }

    public function getFileContent($form_name, $num_file = 0)
    {
        return $this->files[$form_name];
    }

    public function save($filename, $save_path, $num_file = 0)
    {
        throw new Exception("Not implemented");
    }

    public function getAll()
    {
        return $this->files;
    }
}
