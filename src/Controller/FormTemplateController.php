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

    #[Route('/form-template/{id}/view', name: 'form_template_view')]
    public function view(FormTemplate $formTemplate): Response
    {
        $user = $this->getUser();

        if (!$formTemplate->isPublic() && $formTemplate->getOwner() !== $user ) {
            throw $this->createAccessDeniedException('Вы не можете просматривать эту форму.');
        }

        return $this->render('form_templates/view.html.twig', [
            'formTemplate' => $formTemplate,
            'fields' => $formTemplate->getFields(),
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




}


