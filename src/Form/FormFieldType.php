<?php

namespace App\Form;

use App\Entity\FormField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, ['label' => 'Название поля'])
            ->add('type', ChoiceType::class, [
                'label' => 'Тип поля',
                'choices' => [
                    'Text' => 'text',
                    'Checkbox' => 'checkbox',
                    'Radio' => 'radio',
                    'Select' => 'select',
                ]


            ])
            ->add('optionsRaw', TextareaType::class, [
                'label' => 'Опции (JSON или через запятую)',
                'required' => false,
                'help' => 'Введите JSON-массив или список через запятую для select/radio',
            ])
            ->add('required', CheckboxType::class, [
                'label' => 'Обязательное',
                'required' => false,
            ])
            ->add('order', IntegerType::class, ['label' => 'Порядок'])
            ->add('save', SubmitType::class, ['label' => 'Добавить поле']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormField::class,
        ]);
    }
}
