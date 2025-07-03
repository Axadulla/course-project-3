<?php

namespace App\Controller;

use App\Entity\FormTemplate;
use App\Entity\Like;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LikeController extends AbstractController
{
    #[Route('/form/{id}/like', name: 'form_like')]
    #[IsGranted('ROLE_USER')]
    public function like(
        FormTemplate $form,
        LikeRepository $likeRepository,
        EntityManagerInterface $em
    ): RedirectResponse {
        $user = $this->getUser();

        $existingLike = $likeRepository->findOneBy([
            'form' => $form,
            'author' => $user,
        ]);

        if ($existingLike) {
            $em->remove($existingLike);
        } else {
            $like = new Like();
            $like->setAuthor($user);
            $like->setForm($form);
            $em->persist($like);
        }

        $em->flush();

        return $this->redirectToRoute('form_template_view', ['id' => $form->getId()]);
    }
}
