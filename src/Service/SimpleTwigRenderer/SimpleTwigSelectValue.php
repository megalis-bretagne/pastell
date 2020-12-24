<?php

namespace Pastell\Service\SimpleTwigRenderer;

use DonneesFormulaire;
use Exception;
use Flow\JSONPath\JSONPath;
use Twig\TwigFunction;

class SimpleTwigSelectValue implements ISimpleTwigFunction
{
    private const SELECT_VALUE = "select_value";

    public function getFunctionName(): string
    {
        return self::SELECT_VALUE;
    }

    public function getFunction(DonneesFormulaire $donneesFormulaire): TwigFunction
    {
        return new TwigFunction(
            self::SELECT_VALUE,
            function ($element_id) use ($donneesFormulaire) {
                return $donneesFormulaire->getSelectValue($element_id);
            }
        );
    }
}
