<?php

namespace App\Controller;

use App\Entity\FormTemplate;
use App\Entity\FormField;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints as Assert;
use App\Form\FormFieldType;
use App\Repository\FormTemplateRepository;


final class FormTemplateController extends AbstractController
{
    #[Route('/form-template/new', name: 'form_template_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $formTemplate = new FormTemplate();
        $formTemplate->setCreatedAt(new \DateTimeImmutable());
        $formTemplate->setOwner($this->getUser()); // 👈

        $form = $this->createFormBuilder($formTemplate)
            ->add('title')
            ->add('description')
            ->add('isPublic')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($formTemplate);
            $em->flush();

            return $this->redirectToRoute('form_field_edit', ['id' => $formTemplate->getId()]);
        }

        return $this->render('form_templates/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/form-template/{id}/fields', name: 'form_field_edit')]

    public function editFields(FormTemplate $formTemplate, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->getUser() !== $formTemplate->getOwner()) {
            throw $this->createAccessDeniedException();
        }

        $field = new FormField();
        $field->setFormTemplate($formTemplate);


        $form = $this->createForm(FormFieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Обрабатываем optionsRaw в options
            $raw = $field->getOptionsRaw();
            if (!empty($raw)) {
                // Пытаемся декодировать JSON
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $field->setOptions($decoded);
                } else {
                    // Если не JSON, пытаемся через запятую
                    $field->setOptions(array_map('trim', explode(',', $raw)));
                }
            } else {
                $field->setOptions(null);
            }

            $em->persist($field);
            $em->flush();

            return $this->redirectToRoute('form_field_edit', ['id' => $formTemplate->getId()]);
        }

        return $this->render('form_templates/edit.html.twig', [
            'formTemplate' => $formTemplate,
            'form' => $form->createView(),
            'fields' => $formTemplate->getFields()->toArray(),
        ]);
    }

    #[Route('/form-template/{id}/view', name: 'form_template_view')]
    public function view(FormTemplate $formTemplate): Response
    {
        return $this->render('form_templates/view.html.twig', [
            'formTemplate' => $formTemplate,
            'fields' => $formTemplate->getFields(),
        ]);
    }

    #[Route('/form-template', name: 'form_template_index')]
    public function index(FormTemplateRepository $formTemplateRepository): Response
    {
        $forms = $formTemplateRepository->findBy([], ['id' => 'ASC']);

        return $this->render('form_templates/profile.html.twig', [
            'forms' => $forms,
        ]);
    }

    #[Route('/form-template/{id}/delete', name: 'form_template_delete', methods: ['POST'])]
    public function delete(FormTemplate $formTemplate, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_form_' . $formTemplate->getId(), $request->request->get('_token'))) {
            $em->remove($formTemplate);
            $em->flush();
        }

        return $this->redirectToRoute('form_template_index');
    }



    #[Route('/form-template/{id}/edit-template', name: 'form_template_edit')]
    public function editTemplate(FormTemplate $formTemplate, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder($formTemplate)
            ->add('title')
            ->add('description')
            ->add('isPublic')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Устанавливаем новое значение updatedAt
            $formTemplate->setUpdatedAt(new \DateTimeImmutable());

            $em->flush();
            return $this->redirectToRoute('form_template_view', ['id' => $formTemplate->getId()]);
        }

        return $this->render('form_templates/edit_template.html.twig', [
            'form' => $form->createView(),
            'formTemplate' => $formTemplate,
        ]);
    }




}


