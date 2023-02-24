<?php

/**
 * Class AgapeFile
 * Attention, bien qu'on pourrait penser que les fichiers Agape soit de simples fichiers XSD, en fait,
 * il ne respecte pas le schéma XSD !
 */
class AgapeFile extends XMLFile
{
    public const XSD_PREFIX = "xsd";
    public const XSD_SHEMA = "http://www.w3.org/2001/XMLSchema";


    protected function getFromFunction($data, $function)
    {
        $xml = parent::getFromFunction($data, $function);
        $xml->registerXPathNamespace(self::XSD_PREFIX, self::XSD_SHEMA);
        return $xml;
    }

    public function getAllAnnotation($agape_file_path)
    {
        $xml = $this->getFromFilePath($agape_file_path);
        return $xml->xpath("//xsd:annotation");
    }
}
