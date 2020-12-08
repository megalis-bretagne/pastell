<?php

namespace Pastell\Service\SimpleTwigRenderer;

use DonneesFormulaire;
use Twig\TwigFunction;

class SimpleTwigCSVpath implements ISimpleTwigFunction
{
    private const CSVPATH_FUNCTION = "csvpath";

    public function getFunctionName(): string
    {
        return self::CSVPATH_FUNCTION;
    }

    public function getFunction(DonneesFormulaire $donneesFormulaire): TwigFunction
    {
        return new TwigFunction(
            self::CSVPATH_FUNCTION,
            function ($element_id, $column, $line, $delimiter = ',', $enclosure = '"', $escape = "\\") use ($donneesFormulaire) {
                if (! file_exists($donneesFormulaire->getFilePath($element_id))) {
                    return '';
                }
                $lines = file($donneesFormulaire->getFilePath($element_id));
                if (! isset($lines[$line])) {
                    return '';
                }
                $data = str_getcsv($lines[$line], $delimiter, $enclosure, $escape);
                if (! isset($data[$column])) {
                    return '';
                }
                return $data[$column];
            }
        );
    }
}
