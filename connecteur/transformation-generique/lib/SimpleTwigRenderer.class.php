<?php

use Flow\JSONPath\JSONPath;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Extension\SandboxExtension;
use Twig\Loader\ArrayLoader;
use Twig\Sandbox\SecurityPolicy;
use Twig\TwigFunction;

class SimpleTwigRenderer
{
    public const XPATH_FUNCTION = 'xpath';
    public const JSONPATH_FUNCION = 'jsonpath';

    private const AUTHORIZED_TWIG_TAGS = ['if','for'];
    private const AUHTORIZED_TWIG_FILTERS = ['escape'];
    private const AUHTORIZED_TWIG_METHODS = [];
    private const AUHTORIZED_TWIG_PROPERTIES = [];
    private const AUHTORIZED_TWIG_FUNCTIONS = [];


    /**
     * @param string $template_as_string
     * @param DonneesFormulaire $donneesFormulaire
     * @return string
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function render(string $template_as_string, DonneesFormulaire $donneesFormulaire): string
    {
        $policy = new SecurityPolicy(
            self::AUTHORIZED_TWIG_TAGS,
            self::AUHTORIZED_TWIG_FILTERS,
            self::AUHTORIZED_TWIG_METHODS,
            self::AUHTORIZED_TWIG_PROPERTIES,
            self::AUHTORIZED_TWIG_FUNCTIONS
        );
        $sandbox = new SandboxExtension($policy);

        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension($sandbox);

        $function = new TwigFunction(
            self::XPATH_FUNCTION,
            function ($element_id, $xpath_expression) use ($donneesFormulaire) {
                $file_content = $donneesFormulaire->getFileContent($element_id);
                $xml = simplexml_load_string($file_content);
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
        $twigEnvironment->addFunction($function);

        $function = new TwigFunction(
            self::JSONPATH_FUNCION,
            function ($element_id, $json_path_expression) use ($donneesFormulaire) {
                $file_content = $donneesFormulaire->getFileContent($element_id);
                try {
                    $jsonPath = new JSONPath(json_decode($file_content, true));
                } catch (Exception $e) {
                    return '';
                }
                $json_result = $jsonPath->find($json_path_expression);
                if (empty($json_result[0])) {
                    return '';
                }
                return $json_result[0];
            }
        );
        $twigEnvironment->addFunction($function);

        return $twigEnvironment
            ->createTemplate($template_as_string)
            ->render($donneesFormulaire->getRawDataWithoutPassword());
    }
}
