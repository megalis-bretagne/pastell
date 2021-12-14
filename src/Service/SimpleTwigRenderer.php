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
use Twig\TwigFilter;
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
    public function render(string $template_as_string, DonneesFormulaire $donneesFormulaire, array $other_metadata = []): string
    {
        $policy = new SecurityPolicy(
            self::AUTHORIZED_TWIG_TAGS,
            self::AUTHORIZED_TWIG_FILTERS,
            self::AUTHORIZED_TWIG_METHODS,
            self::AUTHORIZED_TWIG_PROPERTIES,
            self::AUTHORIZED_TWIG_FUNCTIONS
        );
        $sandbox = new SandboxExtension($policy);

        $twigEnvironment = new Environment(new ArrayLoader(), ['autoescape' => false]);
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

        $twigEnvironment->addFilter(new TwigFilter('ls_unique', function (array $array) {
            return array_unique($array);
        }));

        set_error_handler([$this, "twigNoticeAsError"]);
        $all_metadata = array_merge($other_metadata, $donneesFormulaire->getRawDataWithoutPassword());

        try {
            $result = $twigEnvironment
                ->createTemplate($template_as_string)
                ->render($all_metadata);
        } catch (SyntaxError $e) {
            throw new UnrecoverableException($this->getFancyErrorMessage($e), $e->getCode(), $e);
        } catch (\Exception $e) {
            throw new UnrecoverableException("Erreur sur le template $template_as_string : " . $e->getMessage());
        } finally {
            restore_error_handler();
        }

        return $result;
    }

    private function getFancyErrorMessage(SyntaxError $e): string
    {

        $all_line = explode("\n", $e->getSourceContext()->getCode());
        foreach ($all_line as $i => $line) {
            $all_line[$i] = sprintf("%d. %s", $i + 1, $line);
        }

        $all_line[$e->getTemplateLine() - 1] = sprintf(
            "\n\n<b>%s</b><em>^^^ %s</em>\n\n",
            $all_line[$e->getTemplateLine() - 1],
            $e->getRawMessage()
        );

        $errorMessage = sprintf(
            "Erreur de syntaxe sur le template twig ligne %d\nMessage d'erreur : %s\n\n%s",
            $e->getTemplateLine(),
            $e->getRawMessage(),
            implode("", $all_line)
        );
        return nl2br($errorMessage);
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
