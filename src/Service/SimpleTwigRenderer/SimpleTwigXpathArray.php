<?php

namespace Pastell\Service\SimpleTwigRenderer;

use DonneesFormulaire;
use Twig\TwigFunction;

class SimpleTwigXpathArray implements ISimpleTwigFunction
{
    private const XPATH_ARRAY_FUNCTION = "xpath_array";

    public function getFunctionName(): string
    {
        return self::XPATH_ARRAY_FUNCTION;
    }

    public function getFunction(DonneesFormulaire $donneesFormulaire): TwigFunction
    {
        return new TwigFunction(
            self::XPATH_ARRAY_FUNCTION,
            function ($element_id, $xpath_expression) use ($donneesFormulaire) {
                $simpleTwigXpathCommon = new SimpleTwigXpathCommon();
                return $simpleTwigXpathCommon->doXpath($element_id, $xpath_expression, $donneesFormulaire);
            }
        );
    }
}
