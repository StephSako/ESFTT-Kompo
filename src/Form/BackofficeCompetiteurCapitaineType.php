<?php

namespace App\Form;

use App\Entity\Competiteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BackofficeCompetiteurCapitaineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'uppercase',
                    'maxlength' => 50
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'maxlength' => 50
                ]
            ])
            ->add('visitor', CheckboxType::class, [
                'label' => 'Compte visiteur (accès restreints)',
                'required' => false
            ])
            ->add('classementOfficiel', IntegerType::class, [
                'label' => false,
                'required' => false,
                    'attr' => [
                        'min' => 500,
                        'max' => 20000
                    ]
                ]
            )
            ->add('imageFile', FileType::class, [
                'label' => false,
                'required' => false
                ]
            )
            ->add('username', TextType::class, [
                'label' => false,
                'required' => true,
                    'attr' => [
                        'maxlength' => 50
                    ]
                ]
            )
            ->add('licence', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'maxlength' => 11
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Competiteur::class,
            'translation_domain' => 'forms'
        ]);
    }
}