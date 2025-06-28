<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ThemeController extends AbstractController
{
    #[Route('/switch-theme', name: 'app_switch_theme', methods: ['POST'])]
    public function switchTheme(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $currentTheme = $session->get('theme', 'light');
        $session->set('theme', $currentTheme === 'light' ? 'dark' : 'light');

        return $this->redirect($request->headers->get('referer') ?? '/');
    }
}
