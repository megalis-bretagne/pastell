<?php

declare(strict_types=1);

namespace Pastell\Controller;

use FrontController;
use ObjectInstancierFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class LegacyController extends AbstractController
{
    public function loadLegacyScript(string $requestPath, string $legacyScript): Response
    {
        return $this->render(
            'legacy.html.twig',
            [
                'requestPath' => $requestPath,
                'legacyScript' => $legacyScript,
            ]
        );
    }

    public function legacy(string $requestPath, string $legacyScript): Response
    {
        $_SERVER['PHP_SELF'] = $requestPath;
        $_SERVER['SCRIPT_NAME'] = $requestPath;
        $_SERVER['SCRIPT_FILENAME'] = $legacyScript;

        \chdir(\dirname($legacyScript));

        $objectInstancier = ObjectInstancierFactory::getObjetInstancier();
        $frontController = new FrontController($objectInstancier);

        $frontController->setGetParameter($_GET);
        $frontController->setPostParameter($_POST);
        $frontController->setServerInfo($_SERVER);
        $frontController->setTwigEnvironment($this->container->get('twig'));
        $objectInstancier->setInstance(Environment::class, $this->container->get('twig'));

        \ob_start();
        $frontController->dispatch();
        $content = (string)\ob_get_clean();

        return new Response($content);
    }
}