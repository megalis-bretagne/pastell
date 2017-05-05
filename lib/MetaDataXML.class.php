<?php

class MetaDataXML {

    public function getMetaDataAsXML(DonneesFormulaire $donneesFormulaire){

        $fluxXML = new SimpleXMLElement("<flux></flux>");

        $rawData = $donneesFormulaire->getRawData();
        foreach($rawData as $name => $value) {
            if (is_array($value)){
                $files = $fluxXML->addChild('files');
                $files->addAttribute('name',$name);
                foreach($value as $num => $file_name){
                    $file = $files->addChild('file');
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

}