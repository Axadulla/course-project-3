<?php

namespace App\Controller;

use App\Entity\FormField;
use App\Form\FormFieldType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class FormFieldController extends AbstractController
{
    #[Route('/form-field/{id}/edit', name: 'form_field_update')]
    public function updateField(FormField $field, Request $request, EntityManagerInterface $em): Response
    {
        // Преобразуем массив options в строку для редактирования
        $field->setOptionsRaw($field->getOptions() ? implode(', ', $field->getOptions()) : '');

        $form = $this->createForm(FormFieldType::class, $field);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Обновляем поле options из optionsRaw
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
            'fields' => $field->getFormTemplate()->getFields(),
        ]);
    }

}
