<?php

namespace Pastell\Service\SimpleTwigRenderer;

use DonneesFormulaire;
use Twig\TwigFunction;

class SimpleTwigXpathArray implements ISimpleTwigFunction
{
    public const XPATH_ARRAY_FUNCTION = 'xpath_array';

    public function getFunctionName(): string
    {
        return self::XPATH_ARRAY_FUNCTION;
    }

    public function getFunction(DonneesFormulaire $donneesFormulaire): TwigFunction
    {
        return new TwigFunction(
            self::XPATH_ARRAY_FUNCTION,
            function ($element_id, $xpath_expression) use ($donneesFormulaire) {
                return (new SimpleTwigXpathCommon())->doXpath($element_id, $xpath_expression, $donneesFormulaire);
            }
        );
    }
}
