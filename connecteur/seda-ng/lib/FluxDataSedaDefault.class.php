<?php

class FluxDataSedaDefault extends FluxData
{
    protected $donneesFormulaire;
    protected $file_list;

    private $metadata;

    private $filenameCount = [];
    private $sha256Count = [];
    private $filePathCount = [];
    private $contentTypeCount = [];
    private $sizeCount = [];
    private $dataCount = [];

    private $zip_file_list = [];
    private $archive_content;

    public function __construct(DonneesFormulaire $donneesFormulaire)
    {
        $this->donneesFormulaire = $donneesFormulaire;
        $this->file_list = array();
    }

    public function getFileList()
    {
        return $this->file_list;
    }

    public function setFileList($key, $filename, $filepath)
    {
        $this->file_list[] = array(
            'key' => $key,
            'filename' => $filename,
            'filepath' => $filepath
        );
    }

    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @param $key
     * @return array|string
     * @throws Exception
     */
    public function getData($key)
    {
        if (isset($this->metadata[$key])) {
            return $this->metadata[$key];
        }

        $method = "get_$key";
        if (method_exists($this, $method)) {
            return $this->$method($key);
        }

        $field = $this->donneesFormulaire->getFormulaire()->getField($key);
        if ($field && $field->getType() == 'select') {
            $select = $field->getSelect();
            if (isset($select[$this->donneesFormulaire->get($key)])) {
                return $select[$this->donneesFormulaire->get($key)];
            }
        }

        if ($this->isZipElement($key)) {
            $sub_command = $this->getZipSubCommand($key);
            $element = $sub_command[1];
            $local_archive_content = $this->getArchiveContent($sub_command[0]);
            if (empty($this->dataCount[$key])) {
                $this->dataCount[$key] = 0;
            }
            return $local_archive_content[$element][$this->dataCount[$key]++];
        }

        return $this->donneesFormulaire->get($key);
    }

    /**
     * @param $key
     * @return array
     * @throws UnrecoverableException
     * @throws Exception
     */
    protected function getArchiveContent($key)
    {
        if (!$this->archive_content) {
            $tmpFolder = new TmpFolder();
            $tmp_folder = $tmpFolder->create();

            $this->extractZipStructure = new ExtractZipStructure();
            $this->extractZipStructure->setNbRecusionLevelStop(ExtractZipStructure::MAX_RECURSION_LEVEL);

            try {
                copy($this->donneesFormulaire->getFilePath($key), $tmp_folder . "/archive.zip");
                $this->archive_content = $this->extractZipStructure->extract(
                    $tmp_folder . "/archive.zip"
                );
            } finally {
                $tmpFolder->delete($tmp_folder);
            }
        }
        return $this->archive_content;
    }

    public function get_transfert_id()
    {
        return md5(time() . mt_rand(0, mt_getrandmax()));
    }

    /**
     * @param $key
     * @return mixed
     * @throws Exception
     */
    public function getFilename($key)
    {
        if (empty($this->filenameCount[$key])) {
            $this->filenameCount[$key] = 0;
        }
        if ($this->isZipElement($key)) {
            $sub_command = $this->getZipSubCommand($key);
            $local_archive_content = $this->getArchiveContent($sub_command[0]);
            return $local_archive_content['file_list'][$this->filenameCount[$key]++];
        }
        return $this->donneesFormulaire->getFileName($key, $this->filenameCount[$key]++);
    }


    /**
     * @param $key
     * @param $key_type
     * @return string
     * @throws Exception
     */
    private function getFilePathFromZipArchive($key, $key_type)
    {
        $sub_command = $this->getZipSubCommand($key);
        $local_archive_content = $this->getArchiveContent($sub_command[0]);
        return $local_archive_content['tmp_folder'] . "/" . $local_archive_content['root_directory'] . "/" . $local_archive_content['file_list'][$this->$key_type[$key]++];
    }

    private function isZipElement($key)
    {
        $sub_command = $this->getZipSubCommand($key);
        if (in_array($sub_command[0], $this->zip_file_list)) {
            return true;
        }
        return false;
    }

    private function getZipSubCommand($key)
    {
        return explode(':', $key, 2);
    }

    /**
     * @param $key
     * @return string
     * @throws Exception
     */
    public function getFileSHA256($key)
    {
        if (empty($this->sha256Count[$key])) {
            $this->sha256Count[$key] = 0;
        }
        if ($this->isZipElement($key)) {
            return hash_file(
                'sha256',
                $this->getFilePathFromZipArchive($key, 'sha256Count')
            );
        }

        $file_path = $this->donneesFormulaire->getFilePath($key, $this->sha256Count[$key]++);
        return hash_file("sha256", $file_path);
    }

    /**
     * @param $key
     * @return string
     * @throws Exception
     */
    public function getFilePath($key)
    {
        if (empty($this->filePathCount[$key])) {
            $this->filePathCount[$key] = 0;
        }

        if ($this->isZipElement($key)) {
            return $this->getFilePathFromZipArchive($key, 'filePathCount');
        }

        return $this->donneesFormulaire->getFilePath($key, $this->filePathCount[$key]++);
    }

    /**
     * @param $key
     * @return bool|string
     * @throws Exception
     */
    public function getContentType($key)
    {
        if (empty($this->contentTypeCount[$key])) {
            $this->contentTypeCount[$key] = 0;
        }

        if ($this->isZipElement($key)) {
            $file_path = $this->getFilePathFromZipArchive($key, 'contentTypeCount');
            $fileContentType = new FileContentType();
            $content_type = $fileContentType->getContentType($file_path);
            if ($content_type == 'inode/x-empty') {
                $content_type = "text/plain";
            }
            return $content_type;
        }


        return $this->donneesFormulaire->getContentType($key, $this->contentTypeCount[$key]++);
    }


    /**
     * @param $key
     * @return false|int
     * @throws DonneesFormulaireException
     * @throws Exception
     */
    public function getFilesize($key)
    {
        if (empty($this->sizeCount[$key])) {
            $this->sizeCount[$key] = 0;
        }

        if ($this->isZipElement($key)) {
            return filesize($this->getFilePathFromZipArchive($key, 'sizeCount'));
        }

        return $this->donneesFormulaire->getFileSize($key, $this->sizeCount[$key]++);
    }

    public function addZipToExtract($key)
    {
        $this->zip_file_list[] = $key;
    }
}
