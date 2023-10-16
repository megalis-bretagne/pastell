<?php

namespace Pastell\Service\SimpleTwigRenderer;

use DonneesFormulaire;
use SimpleXMLWrapper;
use SimpleXMLWrapperException;
use Twig\TwigFunction;
use UnrecoverableException;

class SimpleTwigXpath implements ISimpleTwigFunction
{
    public const XPATH_FUNCTION = 'xpath';

    public function getFunctionName(): string
    {
        return self::XPATH_FUNCTION;
    }

    public function getFunction(DonneesFormulaire $donneesFormulaire): TwigFunction
    {
        return new TwigFunction(
            self::XPATH_FUNCTION,
            function ($element_id, $xpath_expression) use ($donneesFormulaire) {
                $simpleTwigXpathCommon = new SimpleTwigXpathCommon();
                $xml_result =  $simpleTwigXpathCommon->doXpath($element_id, $xpath_expression, $donneesFormulaire);
                if (empty($xml_result[0])) {
                    return '';
                }
                return (string)$xml_result[0];
            }
        );
    }
}
