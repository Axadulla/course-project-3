<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
final class LanguangeController extends AbstractController
{

    #[Route('/switch-language', name: 'app_switch_language', methods: ['POST'])]
    public function switchLanguage(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $current = $session->get('_locale', 'en');
        $session->set('_locale', $current === 'en' ? 'ru' : 'en');

        return $this->redirect($request->headers->get('referer') ?? '/');
    }
}
