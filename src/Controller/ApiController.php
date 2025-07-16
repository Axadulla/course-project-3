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


    #[Route('/api/aggregated/templates', name: 'api_aggregated_templates', methods: ['GET'])]
    public function aggregatedTemplates(
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

        $result = [];

        foreach ($templates as $template) {
            $aggregated = [
                'id' => $template->getId(),
                'title' => $template->getTitle(),
                'createdAt' => $template->getCreatedAt()?->format('Y-m-d H:i:s'),
                'questions' => [],
            ];

            foreach ($template->getFields() as $field) {
                $questionData = [
                    'label' => $field->getLabel(),
                    'type' => $field->getType(),
                    'answersCount' => count($field->getAnswers()),
                    'aggregates' => $this->aggregateField($field),
                ];

                // Тут нужно будет считать статистику (min/max/avg/модальные значения)
                // Пока можно оставить как TODO

                $aggregated['questions'][] = $questionData;
            }

            $result[] = $aggregated;
        }

        return $this->json($result);
    }


    private function aggregateField(\App\Entity\FormField $field): ?array
    {
        $answers = $field->getAnswers();

        if (count($answers) === 0) {
            return null;
        }

        $values = array_map(fn($a) => $a->getValue(), $answers->toArray());

        switch ($field->getType()) {
            case 'number':
                $numbers = array_map('floatval', $values);
                return [
                    'min' => min($numbers),
                    'max' => max($numbers),
                    'avg' => round(array_sum($numbers) / count($numbers), 2),
                ];

            case 'text':
                $counts = array_count_values($values);
                arsort($counts);
                return ['topAnswers' => array_slice(array_keys($counts), 0, 3)];

            case 'select':
            case 'radio':
            case 'checkbox':
                $counts = array_count_values($values);
                return ['optionsSummary' => $counts];
        }

        return null;
    }


}

