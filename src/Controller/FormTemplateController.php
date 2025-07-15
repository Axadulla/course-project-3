<?php

namespace App\Controller;

use App\Entity\FormTemplate;
use App\Entity\FormField;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\FormFieldType;
use App\Repository\FormTemplateRepository;
use App\Entity\Comment;
use App\Form\CommentType;


final class FormTemplateController extends AbstractController
{
    #[Route('/form-template/new', name: 'form_template_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $formTemplate = new FormTemplate();
        $formTemplate->setCreatedAt(new \DateTimeImmutable());
        $formTemplate->setOwner($this->getUser());

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
        if (
            $this->getUser() !== $formTemplate->getOwner()
            && !$this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            throw $this->createAccessDeniedException();
        }


        $field = new FormField();
        $field->setFormTemplate($formTemplate);


        $form = $this->createForm(FormFieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $raw = $field->getOptionsRaw();
            if (!empty($raw)) {

                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $field->setOptions($decoded);
                } else {

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

    #[Route('/form-template/{id}/view', name: 'form_template_view', methods: ['GET', 'POST'])]
    public function view(FormTemplate $formTemplate, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$formTemplate->isPublic() && $formTemplate->getOwner() !== $user && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }


        if ($request->isMethod('POST') && !$request->request->has('comment')) {
            $submission = new \App\Entity\FormSubmission();
            $submission->setTemplate($formTemplate);
            $submission->setUser($user);
            $submission->setSubmittedAt(new \DateTimeImmutable());

            foreach ($formTemplate->getFields() as $field) {
                $fieldName = strtolower(str_replace(' ', '_', $field->getLabel()));
                $value = $request->request->get($fieldName);

                if ($value !== null) {
                    $answer = new \App\Entity\FormAnswer();
                    $answer->setField($field);
                    $answer->setSubmission($submission);
                    $answer->setValue(is_array($value) ? json_encode($value) : (string)$value);

                    $submission->getAnswers()->add($answer);
                    $em->persist($answer);
                }
            }

            $em->persist($submission);
            $em->flush();

            return $this->redirectToRoute('form_template_view', ['id' => $formTemplate->getId()]);
        }

        $comment = new Comment();
        $comment->setForm($formTemplate);
        $comment->setAuthor($user);
        $comment->setCreatedAt(new \DateTimeImmutable());

        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('form_template_view', ['id' => $formTemplate->getId()]);
        }

        return $this->render('form_templates/view.html.twig', [
            'formTemplate' => $formTemplate,
            'fields' => $formTemplate->getFields(),
            'commentForm' => $commentForm->createView(),
        ]);
    }



    #[Route('/form-template', name: 'form_template_index')]
    public function index(FormTemplateRepository $formTemplateRepository): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $forms = $formTemplateRepository->findBy([], ['id' => 'ASC']);
        } else {
            $forms = $formTemplateRepository->createQueryBuilder('f')
                ->where('f.isPublic = true')
                ->orWhere('f.owner = :user')
                ->setParameter('user', $user)
                ->orderBy('f.id', 'ASC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('form_templates/index.html.twig', [
            'forms' => $forms,
        ]);
    }

    #[Route('/forms/delete/bulk', name: 'form_template_bulk_delete', methods: ['POST'])]
    public function bulkDelete(Request $request, EntityManagerInterface $em, FormTemplateRepository $repo): Response
    {
        $selected = $request->request->all('selected');

        if (!empty($selected)) {
            foreach ($selected as $id) {
                $form = $repo->find($id);
                if ($form && ($form->getOwner() === $this->getUser() || $this->isGranted('ROLE_SUPER_ADMIN'))) {
                    $em->remove($form);
                }
            }
            $em->flush();
        } else {
            $this->addFlash();
        }

        return $this->redirectToRoute('form_template_index');
    }


    #[Route('/form-template/{id}/delete', name: 'form_template_delete', methods: ['POST'])]
    public function delete(FormTemplate $formTemplate, EntityManagerInterface $em, Request $request): Response
    {
        if (
            $this->getUser() !== $formTemplate->getOwner()
            && !$this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            throw $this->createAccessDeniedException();
        }


        if ($this->isCsrfTokenValid('delete_form_' . $formTemplate->getId(), $request->request->get('_token'))) {
            $em->remove($formTemplate);
            $em->flush();
        }

        return $this->redirectToRoute('form_template_index');
    }



    #[Route('/form-template/{id}/edit-template', name: 'form_template_edit')]
    public function editTemplate(FormTemplate $formTemplate, Request $request, EntityManagerInterface $em): Response
    {
        if (
            $this->getUser() !== $formTemplate->getOwner()
            && !$this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            throw $this->createAccessDeniedException();
        }


        $form = $this->createFormBuilder($formTemplate)
            ->add('title')
            ->add('description')
            ->add('isPublic')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formTemplate->setUpdatedAt(new \DateTimeImmutable());

            $em->flush();
            return $this->redirectToRoute('form_template_view', ['id' => $formTemplate->getId()]);
        }

        return $this->render('form_templates/edit_template.html.twig', [
            'form' => $form->createView(),
            'formTemplate' => $formTemplate,
        ]);
    }

    #[Route('/form-template/search', name: 'form_template_search')]
    public function search(
        Request $request,
        FormTemplateRepository $formTemplateRepository
    ): Response {
        $query = $request->query->get('q', '');
        $user = $this->getUser();

        $results = $formTemplateRepository->searchByTitle(
            $query,
            $user,
            $this->isGranted('ROLE_SUPER_ADMIN')
        );

        return $this->render('form_templates/search.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);
    }







}


