<?php

namespace Pastell\Service\SimpleTwigRenderer;

use SimpleXMLWrapper;
use SimpleXMLWrapperException;
use UnrecoverableException;

class SimpleTwigXpathCommon
{
    public function doXpath($element_id, $xpath_expression, $donneesFormulaire)
    {
        try {
            $simpleXMLWrapper = new SimpleXMLWrapper();
            $filePath = $donneesFormulaire->getFilePath($element_id);
            if (! $filePath) {
                throw new UnrecoverableException("Le fichier $element_id n'a pas été trouvé");
            }
            $xml = $simpleXMLWrapper->loadFile($filePath);
        } catch (SimpleXMLWrapperException $simpleXMLWrapperException) {
            throw new UnrecoverableException("Le fichier $element_id n'est pas un fichier XML : impossible d'analyser l'expression xpath $xpath_expression");
        }

        return $xml->xpath($xpath_expression);
    }
}