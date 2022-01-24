<?php

/**
 * Class HeliosGeneriqueXMLFile
 * @deprecated PA V3 use PESV2XMLFile instead
 */
class HeliosGeneriqueXMLFile extends PESV2XMLFile
{
    /**
     * @param $file_path
     * @return SimpleXMLElement
     * @throws Exception
     * @deprecated PA V3 use getSimpleXMLFromFile instead
     */
    public function getPESAllerAsSimpleXML($file_path)
    {
        return $this->getSimpleXMLFromFile($file_path);
    }
}
