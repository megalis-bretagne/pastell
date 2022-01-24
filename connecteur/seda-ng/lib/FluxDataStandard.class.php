<?php

/**
 * Class FluxDataStandard
 * @deprecated PA 3.0 use FluxDataSedaDefault instead
 *
 * Cette classe ne permet pas de gérer les fichiers multiples, mais ne peut pas être modifiée à cause des classes qui
 * en hériteraient (le comportement de pastell:file:xxx pourrait être différent dans la class FluxDataSedaDefault)
 */
class FluxDataStandard extends FluxData
{
    protected $donneesFormulaire;
    protected $file_list;

    public function __construct(DonneesFormulaire $donneesFormulaire)
    {
        $this->donneesFormulaire = $donneesFormulaire;
        $this->file_list = array();
    }

    public function getData($key)
    {
        return $this->donneesFormulaire->get($key);
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
            'filepath' => $filepath);
    }

    public function getFilename($key)
    {
        return $this->donneesFormulaire->getFileName($key);
    }

    /**
     * @param $key
     * @return string
     * @throws UnrecoverableException
     */
    public function getFileSHA256($key)
    {
        $file_path =  $this->donneesFormulaire->getFilePath($key);
        if (!file_exists($file_path)) {
            throw new UnrecoverableException(
                "Impossible de trouver le fichier correspondant à l'élément « $key ». Merci de vérifier le profil d'archivage annoté."
            );
        }
        return hash_file("sha256", $file_path);
    }

    public function getFilePath($key)
    {
        return $this->donneesFormulaire->getFilePath($key);
    }

    public function getContentType($key)
    {
        return $this->donneesFormulaire->getContentType($key);
    }

    /**
     * @param $key
     * @return false|int
     */
    public function getFilesize($key)
    {
        return filesize($this->donneesFormulaire->getFilePath($key));
    }

    public function addZipToExtract($key)
    {
    }
}
