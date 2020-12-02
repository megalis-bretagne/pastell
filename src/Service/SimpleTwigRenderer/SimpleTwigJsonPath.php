<?php


namespace Pastell\Service\SimpleTwigRenderer;

use DonneesFormulaire;
use Flow\JSONPath\JSONPath;
use Twig\TwigFunction;
use UnrecoverableException;

class SimpleTwigJsonPath implements ISimpleTwigFunction
{
    private const JSONPATH_FUNCTION = "jsonpath";

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
                    $jsonPath = new JSONPath(json_decode($file_content, true));
                } catch (UnrecoverableException $e) {
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
