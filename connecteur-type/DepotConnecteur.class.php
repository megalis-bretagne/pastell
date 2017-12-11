<?php

/*
 * Cette classe devra à terme remplacer la classe GEDConnecteur actuelle
 */

abstract class DepotConnecteur extends GEDConnecteur {

    /* Les arguments directory_name sont relatifs à l'emplacement défini dans le connecteur  */
    abstract public function listDirectory();
    abstract public function makeDirectory(string $directory_name);
    abstract public function saveDocument(string $directory_name, string $filename, string $filepath);
    abstract public function directoryExists(string $directory_name);
    abstract public function fileExists(string $filename);

    const DEPOT_TYPE_DEPOT = 'depot_type_depot';
    const DEPOT_TYPE_DEPOT_DIRECTORY = 1;
    const DEPOT_TYPE_DEPOT_ZIP = 2;

    const DEPOT_TITRE_REPERTOIRE = 'depot_titre_repertoire';
    const DEPOT_TITRE_REPERTOIRE_TITRE_PASTELL = 1;
    const DEPOT_TITRE_REPERTOIRE_METADATA = 2;

    const DEPOT_TITRE_EXPRESSION = 'depot_titre_expression';

    const DEPOT_METADONNEES = 'depot_metadonnees';
    const DEPOT_METADONNEES_NO_FILE = 1;
    const DEPOT_METADONNEES_XML_FILE = 2;
    const DEPOT_METADONNEES_JSON_FILE = 3;
    const DEPOT_METADONNEES_YAML_FILE = 4;

    const DEPOT_METADONNES_FILENAME = 'depot_metadonnes_filename';

    const DEPOT_METADONNEES_RESTRICTION = 'depot_metadonnees_restriction';

    const DEPOT_PASTELL_FILE_FILENAME = 'depot_pastell_file_filename';
    const DEPOT_PASTELL_FILE_FILENAME_ORIGINAL = 1;
    const DEPOT_PASTELL_FILE_FILENAME_PASTELL = 2;

    const DEPOT_FILE_RESTRICTION = 'depot_file_restriction';

    const DEPOT_FILENAME_REPLACEMENT_REGEXP = 'depot_filename_replacement_regexp';

    const DEPOT_CREATION_FICHIER_TERMINE = 'depot_creation_fichier_termine';

    const DEPOT_NOM_FICHIER_TERMINE = 'depot_nom_fichier_termine';

    const DEPOT_EXISTE_DEJA = 'depot_existe_deja';
    const DEPOT_EXISTE_DEJA_ERROR = 1;
    const DEPOT_EXISTE_DEJA_RENAME = 2;


    private $file_to_save;
    private $directory_name;

    /** @var  TmpFolder $tmpFolder */
    private $tmpFolder;
    private $tmp_folder;

    public function testLecture(){
        return "Contenu du répertoire : " .
            json_encode(
                $this->listDirectory()
            );
    }

    public function testEcriture(){
        $directory_path = 'test_rep_'. mt_rand(0,mt_getrandmax());
        $this->makeDirectory($directory_path);

        if (! $this->directoryExists($directory_path)){
            throw new UnrecoverableException("Le répertoire créé n'a pas été trouvé !");
        }

        $filename = 'test_file_'. mt_rand(0,mt_getrandmax());

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        file_put_contents($tmp_folder."/".$filename,"test de contenu");

        $result =  $this->saveDocument($directory_path,$filename,$tmp_folder."/".$filename);
        $tmpFolder->delete($tmp_folder);
        return $result;
    }

    public function testEcritureFichier(){
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        $filename = 'test_file_'. mt_rand(0,mt_getrandmax());
        file_put_contents($tmp_folder."/".$filename,"test de fichier");
        $result =  $this->saveDocument("",$filename,$tmp_folder."/".$filename);

        if (! $this->fileExists($filename)){
            throw new UnrecoverableException("Le fichier créé n'a pas été trouvé !");
        }

        $tmpFolder->delete($tmp_folder);
        return $result;

    }

    public function send(DonneesFormulaire $donneesFormulaire){
        $this->file_to_save = array();
        $this->createTmpDir();
        try {
            $this->saveFiles($donneesFormulaire);
            $this->saveMetaData($donneesFormulaire);
            $this->finallySave($donneesFormulaire);
            $this->traitementFichierTermine($donneesFormulaire);
        } catch (Exception $e) {
            $this->deleteTmpDir();
            throw $e;
        }
        $this->deleteTmpDir();
        return true;
    }

    private function createTmpDir(){
        $this->tmpFolder = new TmpFolder();
        $this->tmp_folder = $this->tmpFolder->create();
    }

    private function deleteTmpDir(){
        $this->tmpFolder->delete($this->tmp_folder);
    }

    private function saveFiles(DonneesFormulaire $donneesFormulaire){
        $restrict_file_included = $this->getFileIncluded();
        $all_file = $donneesFormulaire->getAllFile();
        foreach($all_file as $field){
            if ($restrict_file_included && ! in_array($field,$restrict_file_included)){
                continue;
            }
            $files = $donneesFormulaire->get($field);
            foreach($files as $num_file => $file_name){
                if ($this->saveFileWithPastellFileName()){
                    $file_name = basename($donneesFormulaire->getFilePath($field,$num_file));
                }
                $this->file_to_save[$file_name] = $donneesFormulaire->getFilePath($field,$num_file);
            }
        }
    }

    private function getMetadataIncluded(){
        if (! $this->connecteurConfig->get(self::DEPOT_METADONNEES_RESTRICTION)){
            return array();
        }
        $result =  explode(",",$this->connecteurConfig->get(self::DEPOT_METADONNEES_RESTRICTION));
        return array_map(function($e){return trim($e);},$result);
    }

    private function getFileIncluded(){
        if (! $this->connecteurConfig->get(self::DEPOT_FILE_RESTRICTION)){
            return array();
        }
        $result =  explode(",",$this->connecteurConfig->get(self::DEPOT_FILE_RESTRICTION));
        return array_map(function($e){return trim($e);},$result);
    }

    private function saveMetaData(DonneesFormulaire $donneesFormulaire){
        $depot_metadonnees = $this->connecteurConfig->get(self::DEPOT_METADONNEES);
        if (! in_array($depot_metadonnees,
            array(self::DEPOT_METADONNEES_YAML_FILE,self::DEPOT_METADONNEES_JSON_FILE,self::DEPOT_METADONNEES_XML_FILE))){
            return;
        }
        $filename = false;
        $extension_filename = '';
        $data = false;
        $raw_data = $donneesFormulaire->getRawData();
        $meta_data_included = $this->getMetadataIncluded();
        if ($meta_data_included){
            foreach($raw_data as $key => $d){
                if (! in_array($key,$meta_data_included)){
                    unset($raw_data[$key]);
                }
            }
        }
        if ($depot_metadonnees == self::DEPOT_METADONNEES_YAML_FILE){
            $data = Spyc::YAMLDump($raw_data);
            $extension_filename = '.txt';
        }
        if ($depot_metadonnees == self::DEPOT_METADONNEES_JSON_FILE){
            $data = json_encode($raw_data);
            $extension_filename = '.json';
        }
        if ($depot_metadonnees == self::DEPOT_METADONNEES_XML_FILE){
            $metaDataXML = new MetaDataXML();
            $data = $metaDataXML->getMetaDataAsXML(
                $donneesFormulaire,
                $this->saveFileWithPastellFileName(),
                $meta_data_included
            );
            $extension_filename = '.xml';
        }
        $filename = "metadata.".$extension_filename;
        if ($this->connecteurConfig->get(self::DEPOT_METADONNES_FILENAME)){
            $filename = $this->getNameFromMetadata($donneesFormulaire,$this->connecteurConfig->get(self::DEPOT_METADONNES_FILENAME)).$extension_filename;
        }
        $metadata_file_path = $this->tmp_folder."/$filename";
        file_put_contents($metadata_file_path,$data);
        $this->file_to_save[$filename] = $metadata_file_path;
    }

    private function saveFileWithPastellFileName(){
        $depot_pastell_file_filename = $this->connecteurConfig->get(self::DEPOT_PASTELL_FILE_FILENAME);
        return  $depot_pastell_file_filename == self::DEPOT_PASTELL_FILE_FILENAME_PASTELL;
    }

    private function finallySave(DonneesFormulaire $donneesFormulaire){
        if ($this->connecteurConfig->get(self::DEPOT_TYPE_DEPOT) == self::DEPOT_TYPE_DEPOT_ZIP){
            $this->saveZip($donneesFormulaire);
        } else {
            $this->saveDirectory($donneesFormulaire);
        }
    }

    private function saveZip(DonneesFormulaire $donneesFormulaire){
        $zip_filename = $this->getDirectoryName($donneesFormulaire).".zip";
        $zip_filename = $this->checkFileExists($zip_filename);

        $zip_filepath = $this->tmp_folder."/".$zip_filename;

        $zip = new ZipArchive();
        $zip->open($zip_filepath, ZIPARCHIVE::CREATE);

        foreach ($this->file_to_save as $filename => $filepath){
            $zip->addFile($filepath,$filename);
        }
        $zip->close();

        $this->saveDocument("",$zip_filename,$zip_filepath);
    }

    private function saveDirectory(DonneesFormulaire $donneesFormulaire){
        $directory_name = $this->getDirectoryName($donneesFormulaire);
        $directory_name = $this->checkDirectoryExists($directory_name);
        $this->directory_name = $directory_name;
        $this->makeDirectory($directory_name);
        foreach ($this->file_to_save as $filename => $filepath){
            $filename = $this->cleaningName($filename);
            $this->saveDocument($directory_name,$filename,$filepath);
        }
    }

    private function checkDirectoryExists($directory_name){
        if (! $this->directoryExists($directory_name)){
            return $directory_name;
        }
        if ($this->connecteurConfig->get(self::DEPOT_EXISTE_DEJA) == self::DEPOT_EXISTE_DEJA_RENAME){
            return $directory_name."_".date("Ymdhis")."_".mt_rand(0,mt_getrandmax());
        }
        throw new UnrecoverableException("Le répertoire $directory_name existe déjà !");
    }

    private function checkFileExists($filename){
        if (! $this->fileExists($filename)){
            return $filename;
        }
        if ($this->connecteurConfig->get(self::DEPOT_EXISTE_DEJA) == self::DEPOT_EXISTE_DEJA_RENAME){
            $basename = pathinfo($filename,PATHINFO_FILENAME);
            $extension = pathinfo($filename,PATHINFO_EXTENSION);
            return $basename."_".date("Ymdhis")."_".mt_rand(0,mt_getrandmax()).".".$extension;
        }
        throw new UnrecoverableException("Le fichier $filename existe déjà !");
    }

    private function getDirectoryName(DonneesFormulaire $donneesFormulaire){
        if (
            $this->connecteurConfig->get(self::DEPOT_TITRE_REPERTOIRE) == self::DEPOT_TITRE_REPERTOIRE_METADATA
            &&
            $this->connecteurConfig->get(self::DEPOT_TITRE_EXPRESSION)
        ){
            $name = $this->getNameFromMetadata($donneesFormulaire,$this->connecteurConfig->get(self::DEPOT_TITRE_EXPRESSION));
        } else {
            $name = $donneesFormulaire->getTitre();
        }
        return $this->cleaningName($name);
    }



    private function getNameFromMetadata(DonneesFormulaire $donneesFormulaire, $expression){
        return preg_replace_callback(
            "#%([^%]*)%#",
            function($matches) use ($donneesFormulaire) {
                return $donneesFormulaire->get($matches[1]);
            },
            $expression
        );
    }

    private function cleaningName($name){
        $regexp = $this->connecteurConfig->get(self::DEPOT_FILENAME_REPLACEMENT_REGEXP)?:'#[\\\\/]#';
        return preg_replace($regexp,"-",$name);
    }

    private function traitementFichierTermine(DonneesFormulaire $donneesFormulaire){
        if (! $this->connecteurConfig->get(self::DEPOT_CREATION_FICHIER_TERMINE)
            || $this->connecteurConfig->get(self::DEPOT_TYPE_DEPOT) == self::DEPOT_TYPE_DEPOT_ZIP
        ){
            return;
        }
        $filename = $this->connecteurConfig->get(self::DEPOT_NOM_FICHIER_TERMINE)?:"fichier_termine.txt";
        $filepath = $this->tmp_folder."/".$filename;
        file_put_contents($filepath,"Le transfert est terminé");
        $this->saveDocument($this->directory_name,$filename,$filepath);
    }
}
