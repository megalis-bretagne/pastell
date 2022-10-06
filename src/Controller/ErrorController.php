<?php

declare(strict_types=1);

namespace Pastell\Controller;

use FrontController;
use ObjectInstancierFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * TODO: Remove and handle exceptions with a twig template
 * templates/bundles/TwigBundle/Exception/error404.html.twg
 */
final class ErrorController extends AbstractController
{
    public function show(): Response
    {
        $frontController = new FrontController(ObjectInstancierFactory::getObjetInstancier());

        $frontController->setGetParameter($_GET);
        $frontController->setPostParameter($_POST);
        $frontController->setServerInfo($_SERVER);
        $frontController->setTwigEnvrionment($this->container->get('twig'));

        \ob_start();
        $frontController->dispatch();
        $content = (string)\ob_get_clean();

        return new Response($content);
    }
}
