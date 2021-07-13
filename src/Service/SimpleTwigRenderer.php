<?php

namespace Pastell\Service;

use Pastell\Helpers\ClassHelper;
use Pastell\Service\SimpleTwigRenderer\ISimpleTwigFunction;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Extension\SandboxExtension;
use Twig\Loader\ArrayLoader;
use Twig\Sandbox\SecurityPolicy;
use DonneesFormulaire;
use UnrecoverableException;

class SimpleTwigRenderer
{
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

        $function_class_list = ClassHelper::findRecursive("Pastell\Service\SimpleTwigRenderer");

        foreach ($function_class_list as $function_class) {
            if (! is_subclass_of($function_class, ISimpleTwigFunction::class)) {
                continue;
            }

            /**
             * @var $simpleTwigFunction ISimpleTwigFunction
             */
            $simpleTwigFunction = new $function_class();
            $twigEnvironment->addFunction($simpleTwigFunction->getFunction($donneesFormulaire));
        }

        set_error_handler([$this, "twigNoticeAsError"]);
        try {
            $result = $twigEnvironment
                ->createTemplate($template_as_string)
                ->render($donneesFormulaire->getRawDataWithoutPassword());
        } catch (\Exception $e) {
            throw new UnrecoverableException("Erreur sur le template $template_as_string : " . $e->getMessage());
        } finally {
            restore_error_handler();
        }


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
