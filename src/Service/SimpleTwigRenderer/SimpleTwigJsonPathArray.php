<?php

namespace Pastell\Service\SimpleTwigRenderer;

use DonneesFormulaire;
use Exception;
use Flow\JSONPath\JSONPath;
use Twig\TwigFunction;

class SimpleTwigJsonPathArray implements ISimpleTwigFunction
{
    public const JSONPATH_ARRAY_FUNCTION = 'jsonpath_array';

    public function getFunctionName(): string
    {
        return self::JSONPATH_ARRAY_FUNCTION;
    }

    public function getFunction(DonneesFormulaire $donneesFormulaire): TwigFunction
    {
        return new TwigFunction(
            self::JSONPATH_ARRAY_FUNCTION,
            function ($element_id, $json_path_expression) use ($donneesFormulaire) {
                $file_content = $donneesFormulaire->getFileContent($element_id);
                try {
                    $jsonPath = new JSONPath(json_decode($file_content, true, 512, JSON_THROW_ON_ERROR));
                } catch (Exception) {
                    return [];
                }
                return $jsonPath->find($json_path_expression);
            }
        );
    }
}
