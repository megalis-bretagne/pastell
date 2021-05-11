<?php

namespace Pastell\Service\SimpleTwigRenderer;

use DonneesFormulaire;
use SimpleXMLWrapper;
use SimpleXMLWrapperException;
use Twig\TwigFunction;
use UnrecoverableException;

class SimpleTwigXpath implements ISimpleTwigFunction
{
    private const XPATH_FUNCTION = "xpath";

    public function getFunctionName(): string
    {
        return self::XPATH_FUNCTION;
    }

    public function getFunction(DonneesFormulaire $donneesFormulaire): TwigFunction
    {
        return new TwigFunction(
            self::XPATH_FUNCTION,
            function ($element_id, $xpath_expression) use ($donneesFormulaire) {

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

                if ($xml === null) {
                    return '';
                }

                $xml_result  = $xml->xpath($xpath_expression);


                if (empty($xml_result[0])) {
                    return '';
                }
                return strval($xml_result[0]);
            }
        );
    }
}
