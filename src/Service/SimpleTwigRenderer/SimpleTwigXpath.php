<?php

namespace Pastell\Service\SimpleTwigRenderer;

use DonneesFormulaire;
use SimpleXMLWrapper;
use SimpleXMLWrapperException;
use Twig\TwigFunction;

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
                    $xml = $simpleXMLWrapper->loadFile($donneesFormulaire->getFilePath($element_id));
                } catch (SimpleXMLWrapperException $simpleXMLWrapperException) {
                    $xml = "";
                }
                if (! $xml) {
                    return '';
                }
                $xml_result  = $xml->xpath($xpath_expression);
                if (empty($xml_result[0])) {
                    return '';
                }
                return $xml_result[0];
            }
        );
    }
}
