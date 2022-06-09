<?php

namespace Pastell\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    #[Route('/about', name: 'app_about')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Controler de test pour symfony',
            'path' => 'src/Controller/AboutController.php',
        ]);
    }
}
