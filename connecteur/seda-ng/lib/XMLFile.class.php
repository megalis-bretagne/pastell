<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'XMLFileException.class.php';

class XMLFile
{
    public function getFromFilePath($file_path)
    {
        return $this->getFromFunction($file_path, "simplexml_load_file");
    }

    public function getFromString($string)
    {
        return $this->getFromFunction($string, "simplexml_load_string");
    }

    protected function getFromFunction($data, $function)
    {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        /** @var SimpleXMLElement $xml */
        $xml = $function($data);

        if (! $xml) {
            $xmlFileException = new XMLFileException("Erreur lors de l'analyse de la chaîne XML ($data)");
            $xmlFileException->last_xml_errors = libxml_get_errors();
            libxml_use_internal_errors($previous);
            throw $xmlFileException;
        }
        libxml_use_internal_errors($previous);
        return $xml;
    }
}
