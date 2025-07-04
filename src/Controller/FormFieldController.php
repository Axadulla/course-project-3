<?php

namespace App\Controller;

use App\Entity\FormField;
use App\Form\FormFieldType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\FormTemplate;
use App\Repository\FormFieldRepository;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

final class FormFieldController extends AbstractController
{
    #[Route('/form-field/{id}/edit', name: 'form_field_update')]
    public function updateField(FormField $field, Request $request, EntityManagerInterface $em, FormFieldRepository $fieldRepo): Response
    {

        $field->setOptionsRaw($field->getOptions() ? implode(', ', $field->getOptions()) : '');

        $form = $this->createForm(FormFieldType::class, $field);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $raw = $field->getOptionsRaw();
            $options = null;

            if ($raw) {
                $decoded = json_decode($raw, true);
                $options = $decoded ?? array_map('trim', explode(',', $raw));
            }

            $field->setOptions($options);
            $em->flush();

            return $this->redirectToRoute('form_template_view', [
                'id' => $field->getFormTemplate()->getId(),
            ]);
        }

        return $this->render('form_field/edit_field.html.twig', [
            'form' => $form->createView(),
            'field' => $field,
            'formTemplate' => $field->getFormTemplate(),
            'fields' => $fieldRepo->findByTemplateOrdered($field->getFormTemplate()),
        ]);
    }

    #[Route('/form/{id}/reorder', name: 'form_field_reorder', methods: ['POST'])]
    public function reorderFields(
        Request $request,
        FormTemplate $formTemplate,
        FormFieldRepository $fieldRepo,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse {
        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('reorder_fields', $token))) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
        }

        $data = json_decode($request->getContent(), true);

        foreach ($data['order'] as $item) {
            $field = $fieldRepo->find($item['id']);
            if ($field && $field->getFormTemplate() === $formTemplate) {
                 $field->setOrder($item['position']);
            }
        }

        $em->flush();

        foreach ($data['order'] as $item) {
            $field = $fieldRepo->find($item['id']);
            if ($field && $field->getFormTemplate() === $formTemplate) {
                $field->setOrder($item['position']);
                dump("Set {$item['position']} to #{$item['id']}");
            }
        }
        $em->flush();


        return new JsonResponse(['status' => 'success']);


    }



}
