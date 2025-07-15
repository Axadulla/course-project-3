<?php


namespace App\Controller;

use App\Entity\ApiToken;
use App\Repository\FormTemplateRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/templates', name: 'api_templates', methods: ['GET'])]
    public function templates(
        Request $request,
        EntityManagerInterface $em,
        FormTemplateRepository $templateRepository,
    ): JsonResponse {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->json(['error' => 'API token required'], 401);
        }

        $tokenValue = substr($authHeader, 7);

        $token = $em->getRepository(ApiToken::class)->findOneBy(['token' => $tokenValue]);

        if (!$token) {
            return $this->json(['error' => 'Invalid API token'], 403);
        }

        $user = $token->getUser();
        $templates = $templateRepository->findBy(['owner' => $user]);

        $data = [];

        foreach ($templates as $template) {
            $data[] = [
                'id' => $template->getId(),
                'title' => $template->getTitle(),
                'createdAt' => $template->getCreatedAt()?->format('Y-m-d H:i:s'),
                'fieldsCount' => count($template->getFields()),
                // 'submissionsCount' => count($template->getSubmissions()),
                // Здесь позже добавим агрегированные данные (min, max, avg и т.д.)
            ];
        }

        return $this->json($data);
    }
}

