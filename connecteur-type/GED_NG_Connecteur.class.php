<?php

/*
 * Cette classe devra à terme remplacer la classe GEDConnecteur actuelle
 */

abstract class GED_NG_Connecteur extends GEDConnecteur {

    abstract public function listDirectory($directory_name);
    abstract public function makeDirectory($directory_name);
    abstract public function saveDocument($directory_name, $filename, $document_content);

    const GED_TYPE_DEPOT = 'ged_type_depot';
    const GED_TYPE_DEPOT_DIRECTORY = 1;
    const GED_TYPE_DEPOT_ZIP = 2;

    const GED_TITRE_REPERTOIRE = 'ged_titre_repertoire';
    const GED_TITRE_REPERTOIRE_TITRE_PASTELL = 1;
    const GED_TITRE_REPERTOIRE_METADATA = 2;

    const GED_TITRE_EXPRESSION = 'ged_titre_expression';

    const GED_METADONNEES = 'ged_metadonnees';
    const GED_METADONNEES_NO_FILE = 1;
    const GED_METADONNEES_XML_FILE = 2;
    const GED_METADONNEES_JSON_FILE = 3;
    const GED_METADONNEES_YAML_FILE = 4;

    const GED_METADONNES_FILENAME = 'ged_metadonnes_filename';

    const GED_METADONNEES_RESTRICTION = 'ged_metadonnees_restriction';

    const GED_PASTELL_FILE_FILENAME = 'ged_pastell_file_filename';
    const GED_PASTELL_FILE_FILENAME_ORIGINAL = 1;
    const GED_PASTELL_FILE_FILENAME_PASTELL = 2;

    const GED_FILE_RESTRICTION = 'ged_file_restriction';

    const GED_FILENAME_REPLACEMENT_REGEXP = 'ged_filename_replacement_regexp';

    const GED_CREATION_FICHIER_TERMINE = 'ged_creation_fichier_termine';

    const GED_NOM_FICHIER_TERMINE = 'ged_nom_fichier_termine';

    private $file_to_save;

    public function testLecture(){
        return "Contenu du répertoire : " .
            json_encode(
                $this->listDirectory("")
            );
    }

    public function testEcriture(){
        $directory_path = 'test_rep_'. mt_rand(0,mt_getrandmax());
        $this->makeDirectory($directory_path);
        $filename = 'test_file_'. mt_rand(0,mt_getrandmax());
        return $this->saveDocument($directory_path,$filename,"test de contenu");
    }


    public function send(DonneesFormulaire $donneesFormulaire){
        $this->file_to_save = array();
        $this->saveFiles($donneesFormulaire);
        $this->saveMetaData($donneesFormulaire);
        $this->finallySave($donneesFormulaire);
        $this->traitementFichierTermine($donneesFormulaire);
        return true;
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
                $content = $donneesFormulaire->getFileContent($field,$num_file);
                if ($this->saveFileWithPastellFileName()){
                    $file_name = basename($donneesFormulaire->getFilePath($field,$num_file));
                }
                $this->file_to_save[$file_name] = $content;
            }
        }
    }

    private function getMetadataIncluded(){
        if (! $this->connecteurConfig->get(self::GED_METADONNEES_RESTRICTION)){
            return array();
        }
        $result =  explode(",",$this->connecteurConfig->get(self::GED_METADONNEES_RESTRICTION));
        return array_map(function($e){return trim($e);},$result);
    }

    private function getFileIncluded(){
        if (! $this->connecteurConfig->get(self::GED_FILE_RESTRICTION)){
            return array();
        }
        $result =  explode(",",$this->connecteurConfig->get(self::GED_FILE_RESTRICTION));
        return array_map(function($e){return trim($e);},$result);
    }

    private function saveMetaData(DonneesFormulaire $donneesFormulaire){
        $ged_metadonnees = $this->connecteurConfig->get(self::GED_METADONNEES);
        if (! in_array($ged_metadonnees,
            array(self::GED_METADONNEES_YAML_FILE,self::GED_METADONNEES_JSON_FILE,self::GED_METADONNEES_XML_FILE))){
            return;
        }
        $filename = false;
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
        if ($ged_metadonnees == self::GED_METADONNEES_YAML_FILE){
            /*$meta_data = $donneesFormulaire->getMetaData();
            $data = preg_replace('#\\\"#', "", $meta_data);*/
            $data = Spyc::YAMLDump($raw_data);
            $filename = "metadata.txt";
        }
        if ($ged_metadonnees == self::GED_METADONNEES_JSON_FILE){
            $data = json_encode($raw_data);
            $filename = "metadata.json";
        }
        if ($ged_metadonnees == self::GED_METADONNEES_XML_FILE){
            $metaDataXML = new MetaDataXML();
            $data = $metaDataXML->getMetaDataAsXML(
                $donneesFormulaire,
                $this->saveFileWithPastellFileName(),
                $meta_data_included
            );
            $filename = "metadata.xml";
        }
        if ($this->connecteurConfig->get(self::GED_METADONNES_FILENAME)){
            $filename = $this->getNameFromMetadata($donneesFormulaire,$this->connecteurConfig->get(self::GED_METADONNES_FILENAME));
        }
        $this->file_to_save[$filename] = $data;
    }

    private function saveFileWithPastellFileName(){
        $ged_pastell_file_filename = $this->connecteurConfig->get(self::GED_PASTELL_FILE_FILENAME);
        return  $ged_pastell_file_filename == self::GED_PASTELL_FILE_FILENAME_PASTELL;
    }

    private function finallySave(DonneesFormulaire $donneesFormulaire){
        if ($this->connecteurConfig->get(self::GED_TYPE_DEPOT) == self::GED_TYPE_DEPOT_ZIP){
            $this->saveZip($donneesFormulaire);
        } else {
            $this->saveDirectory($donneesFormulaire);
        }
    }

    private function saveZip(DonneesFormulaire $donneesFormulaire){

        ob_start();
        $zip = new \ZipStream\ZipStream();

        foreach ($this->file_to_save as $filename => $content){
            $zip->addFile($filename,$content);
        }
        $zip->finish();
        $content = ob_get_contents();
        ob_end_clean();

        $this->saveDocument("",$this->getDirectoryName($donneesFormulaire).".zip",$content);
    }

    private function saveDirectory(DonneesFormulaire $donneesFormulaire){
        $directory_name = $this->getDirectoryName($donneesFormulaire);
        $this->makeDirectory($directory_name);
        foreach ($this->file_to_save as $filename => $content){
            $filename = $this->cleaningName($filename);
            $this->saveDocument($directory_name,$filename,$content);
        }
    }

    private function getDirectoryName(DonneesFormulaire $donneesFormulaire){
        if (
            $this->connecteurConfig->get(self::GED_TITRE_REPERTOIRE) == self::GED_TITRE_REPERTOIRE_METADATA
            &&
            $this->connecteurConfig->get(self::GED_TITRE_EXPRESSION)
        ){
            $name = $this->getNameFromMetadata($donneesFormulaire,$this->connecteurConfig->get(self::GED_TITRE_EXPRESSION));
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
        $regexp = $this->connecteurConfig->get(self::GED_FILENAME_REPLACEMENT_REGEXP)?:'#[\\\\/]#';
        return preg_replace($regexp,"-",$name);
    }

    private function traitementFichierTermine(DonneesFormulaire $donneesFormulaire){
        if (! $this->connecteurConfig->get(self::GED_CREATION_FICHIER_TERMINE)){
            return;
        }
        $filename = $this->connecteurConfig->get(self::GED_NOM_FICHIER_TERMINE)?:"fichier_termine.txt";
        $directory_name = $this->getDirectoryName($donneesFormulaire);
        $this->saveDocument($directory_name,$filename,"Le transfert est terminé");

    }
}
