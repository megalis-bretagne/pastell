<?php

namespace Pastell\Service\SimpleTwigRenderer;

use DonneesFormulaire;
use Exception;
use Flow\JSONPath\JSONPath;
use Twig\TwigFunction;

class SimpleTwigJsonPath implements ISimpleTwigFunction
{
    public const JSONPATH_FUNCTION = 'jsonpath';

    public function getFunctionName(): string
    {
        return self::JSONPATH_FUNCTION;
    }

    public function getFunction(DonneesFormulaire $donneesFormulaire): TwigFunction
    {
        return new TwigFunction(
            self::JSONPATH_FUNCTION,
            function ($element_id, $json_path_expression) use ($donneesFormulaire) {
                $file_content = $donneesFormulaire->getFileContent($element_id);
                try {
                    $jsonPath = new JSONPath(json_decode($file_content, true, 512, JSON_THROW_ON_ERROR));
                } catch (Exception) {
                    return '';
                }
                $json_result = $jsonPath->find($json_path_expression);
                if (empty($json_result[0])) {
                    return '';
                }
                return $json_result[0];
            }
        );
    }
}
