<?php

namespace App\Form;

use App\Entity\Division;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DivisionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shortName', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'validate uppercase',
                    'maxlength' => 5
                ]
            ])
            ->add('longName', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'validate',
                    'maxlength' => 25
                ]
            ])
            ->add('nbJoueursChampParis', IntegerType::class, [
                'invalid_message' => 'Indiquez -1 si division absente en champ. de Paris',
                'empty_data' => 0,
                'label' => false,
                'required' => false,
                'attr' => [
                    'min' => -1,
                    'max' => 9
                ]
            ])
            ->add('nbJoueursChampDepartementale', IntegerType::class, [
                'invalid_message' => 'Indiquez -1 si division absente en champ. depart.',
                'empty_data' => 0,
                'label' => false,
                'required' => false,
                'attr' => [
                    'min' => -1,
                    'max' => 4
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Division::class,
            'translation_domain' => 'forms'
        ]);
    }
}