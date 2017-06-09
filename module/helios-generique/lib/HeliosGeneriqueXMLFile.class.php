<?php

class HeliosGeneriqueXMLFile {


    public function getValueFromXPath(SimpleXMLElement $xml,$xpath_str){
        $result = array();
        $attr_list = $xml->xpath($xpath_str);
        foreach($attr_list as $attr){
            $result[] = utf8_decode(strval($attr));
        }
        $result = array_unique($result);
        return implode(', ',$result);
    }

    public function getPESAllerAsSimpleXML($file_path){
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $xml = simplexml_load_file($file_path,"SimpleXMLElement",LIBXML_PARSEHUGE);

        if (! $xml){
            $message = "";
            foreach(libxml_get_errors() as $xml_error){
                $message .= $xml_error->code . " :" .$xml_error->message."<br/>";
            }

            throw new Exception("Erreur lors de l'analyse du fichier $message");
        }
        libxml_clear_errors();
        return $xml;
    }


}
