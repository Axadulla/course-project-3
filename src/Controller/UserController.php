<?php
// src/Controller/UserController.php

namespace App\Controller;

use App\Repository\FormTemplateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/profile', name: 'user_profile')]
    public function profile(FormTemplateRepository $formTemplateRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $forms = $formTemplateRepository->findBy(['owner' => $user], ['id' => 'DESC']);

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'forms' => $forms,
        ]);
    }
}
