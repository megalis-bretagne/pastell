<?php

namespace Pastell\Service;

use Exception;
use Flow\JSONPath\JSONPath;
use SimpleXMLWrapper;
use SimpleXMLWrapperException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Extension\SandboxExtension;
use Twig\Loader\ArrayLoader;
use Twig\Sandbox\SecurityPolicy;
use Twig\TwigFunction;
use DonneesFormulaire;
use UnrecoverableException;

class SimpleTwigRenderer
{
    public const XPATH_FUNCTION = 'xpath';
    public const JSONPATH_FUNCTION = 'jsonpath';

    private const AUTHORIZED_TWIG_TAGS = ['if','for'];
    private const AUTHORIZED_TWIG_FILTERS = ['escape'];
    private const AUTHORIZED_TWIG_METHODS = [];
    private const AUTHORIZED_TWIG_PROPERTIES = [];
    private const AUTHORIZED_TWIG_FUNCTIONS = [];


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
            self::AUTHORIZED_TWIG_FILTERS,
            self::AUTHORIZED_TWIG_METHODS,
            self::AUTHORIZED_TWIG_PROPERTIES,
            self::AUTHORIZED_TWIG_FUNCTIONS
        );
        $sandbox = new SandboxExtension($policy);

        $twigEnvironment = new Environment(new ArrayLoader());
        $twigEnvironment->addExtension($sandbox);

        $function = new TwigFunction(
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
        $twigEnvironment->addFunction($function);

        $function = new TwigFunction(
            self::JSONPATH_FUNCTION,
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

        set_error_handler([$this, "twigNoticeAsError"]);
        $result = $twigEnvironment
            ->createTemplate($template_as_string)
            ->render($donneesFormulaire->getRawDataWithoutPassword());
        restore_error_handler();

        return $result;
    }

    /**
     * @param $severity
     * @param $message
     * @throws UnrecoverableException
     */
    public function twigNoticeAsError($severity, $message)
    {
        if (!(error_reporting() & $severity)) {
            return;
        }
        throw new UnrecoverableException($message);
    }
}
