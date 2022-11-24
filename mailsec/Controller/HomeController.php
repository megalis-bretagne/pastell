<?php

declare(strict_types=1);

namespace Mailsec\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'mailsec_index', methods: ['GET'])]
    public function index(Request $request): RedirectResponse
    {
        return $this->redirectToRoute('mailsec_recipient_index', ['key' => $request->get('key')]);
    }
}
