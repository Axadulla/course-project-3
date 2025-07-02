<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\FormTemplateRepository;
final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(FormTemplateRepository $formTemplateRepository): Response
    {
        // Последние 4 формы, созданные не более 30 дней назад
        $recentForms = array_filter(
            $formTemplateRepository->findBy([], ['createdAt' => 'DESC'], 10),
            function ($form) {
                $createdAt = $form->getCreatedAt();
                return $createdAt !== null && $createdAt->diff(new \DateTime())->days <= 3;
            }
        );
        $recentForms = array_slice($recentForms, 0, 4);

        // Формы, у которых хотя бы 1 лайк
        $popularForms = array_filter(
            $formTemplateRepository->findAll(),
            fn($form) => count($form->getLikes()) > 0
        );

        // Сортируем по количеству лайков (по убыванию) и берём топ-4
        usort($popularForms, fn($a, $b) => count($b->getLikes()) <=> count($a->getLikes()));
        $popularForms = array_slice($popularForms, 0, 4);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'popularForms' => $popularForms,
            'recentForms' => $recentForms,
        ]);
    }

}
