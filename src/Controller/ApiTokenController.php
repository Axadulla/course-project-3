<?php

namespace App\Controller;

use App\Entity\ApiToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ApiTokenController extends AbstractController
{
    #[Route('/profile/generate-api-token', name: 'generate_api_token')]
    #[IsGranted('ROLE_USER')]
    public function generate(EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();

        // Если токен уже есть — не создаём дубликаты
        if ($user->getApiTokens()->count() === 0) {
            $token = new ApiToken($user);
            $em->persist($token);
            $em->flush();

            $this->addFlash('success', 'API токен успешно сгенерирован!');
        } else {
            $this->addFlash('info', 'У вас уже есть API токен.');
        }

        return $this->redirectToRoute('user_profile');
    }
}
