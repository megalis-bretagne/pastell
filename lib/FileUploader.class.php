<?php

class FileUploader
{
    private $files;
    private $lastError;
    private $dontUseMoveUploadedFile;


    public function __construct()
    {
        $this->setFiles($_FILES);
        $this->setDontUseMoveUploadedFile(false);
    }

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function setDontUseMoveUploadedFile(bool $dontUseMoveUploadedFile)
    {
        $this->dontUseMoveUploadedFile = $dontUseMoveUploadedFile;
    }

    public function getFilePath($filename, $num_file = 0)
    {
        return $this->getValueIntern($filename, 'tmp_name', $num_file);
    }

    public function getName($filename, $num_file = 0)
    {
        return $this->getValueIntern($filename, 'name', $num_file);
    }

    public function getFileContent($form_name, $num_file = 0)
    {
        $tmp_name = $this->getValueIntern($form_name, 'tmp_name', $num_file);
        if (! $tmp_name) {
            return false;
        }
        return file_get_contents($tmp_name);
    }

    public function save($filename, $save_path, $num_file = 0)
    {
        if ($this->dontUseMoveUploadedFile) {
            rename($this->getFilePath($filename, $num_file), $save_path);
        } else {
            move_uploaded_file_wrapper($this->getFilePath($filename, $num_file), $save_path);
        }
    }

    public function getNbFile($form_name)
    {
        if (!isset($this->files[$form_name]['tmp_name'])) {
            $this->lastError = "Fichier $form_name inexistant";
            return false;
        }
        if (is_array($this->files[$form_name]['tmp_name'])) {
            return count($this->files[$form_name]['tmp_name']);
        } else {
            return 1;
        }
    }

    public function getAll()
    {
        $result = array();
        foreach ($this->files as $filename => $value) {
            $result[$filename] = $this->getName($filename);
        }
        return $result;
    }

    private function getValueIntern($name, $key, $num_file = 0)
    {
        if (! isset($this->files[$name][$key])) {
            $this->lastError = "Fichier $name inexistant";
            return false;
        }

        if (is_array($this->files[$name][$key])) {
            if (! isset($this->files[$name][$key][$num_file])) {
                $this->lastError = "Fichier $name:$num_file inexistant";
                return false;
            }
            if ($this->files[$name]['error'][$num_file] != UPLOAD_ERR_OK) {
                $this->lastError = $this->getUploadErrString($this->files[$name]['error'][$num_file]);
                return false;
            }
            return $this->files[$name][$key][$num_file];
        } else {
            if ($this->files[$name]['error'] != UPLOAD_ERR_OK) {
                $this->lastError = $this->getUploadErrString($this->files[$name]['error']);
                return false;
            }
            return $this->files[$name][$key];
        }
    }

    private function getUploadErrString($upload_error_int)
    {
        switch ($upload_error_int) {
            case UPLOAD_ERR_INI_SIZE:
                return "Le fichier dépasse " . ini_get("upload_max_filesize");
            case UPLOAD_ERR_FORM_SIZE:
                return "Le fichier dépasse la taille limite autorisé par le formulaire";
            case UPLOAD_ERR_PARTIAL:
                return "Le fichier n'a été que partiellement reçu";
            case UPLOAD_ERR_NO_FILE:
                return "Aucun fichier n'a été reçu";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Erreur de configuration : le répertoire temporaire n'existe pas";
            case UPLOAD_ERR_CANT_WRITE:
                return "Erreur de configuration : Impossible d'écrire dans le répertoire temporaire";
            case UPLOAD_ERR_EXTENSION:
                return "Une extension PHP empeche l'upload du fichier!";
            default:
                return "Erreur inconnue ($upload_error_int) lors de l'upload du fichier";
        }
    }

    public function getLastError()
    {
        return $this->lastError;
    }
}
