<?php

class MetaDataXML {

    public function getMetaDataAsXML(DonneesFormulaire $donneesFormulaire, $fileNamePastell = false){

        $fluxXML = new SimpleXMLElement("<flux></flux>");

        $rawData = $donneesFormulaire->getRawData();
        foreach($rawData as $name => $value) {
            if (is_array($value)){
                $files = $fluxXML->addChild('files');
                $files->addAttribute('name',$name);
                foreach($value as $num => $file_name){
                    $file = $files->addChild('file');
                    //NON, on ne peut pas supprimer les accents dans les noms de fichiers !
					$file_name = $this->getSanitizeFileName($file_name);
                    if ($fileNamePastell) {
                        $file_path = $donneesFormulaire->getFilePath($name,$num);
                        $file->addAttribute('content',basename($file_path));
                        $file->addAttribute('name_original',$file_name);
                    }
					$file->addAttribute('content',$file_name);
                }
            } else {
                $data = $fluxXML->addChild('data');
                $data->addAttribute('name', $name);
                $data->addAttribute('value', $value);
            }
        }

        $dom = dom_import_simplexml($fluxXML)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();

    }

	public function getSanitizeFileName($file){
		$file = strtr($file," àáâãäçèéêëìíîïñòóôõöùúûüýÿ","_aaaaaceeeeiiiinooooouuuuyy");
		$file = preg_replace('/[^\w-_\.]/',"",$file);
		return $file;
	}

}