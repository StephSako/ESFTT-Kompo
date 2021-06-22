<?php

namespace App\Form;

use App\Entity\Competiteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetiteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'uppercase validate',
                    'maxlength' => 50
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'validate',
                    'maxlength' => 50
                ]
            ])
            ->add('classementOfficiel', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'min' => 500,
                    'max' => 20000
                ]
            ])
            ->add('username', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'validate',
                    'maxlength' => 50
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => false,
                'required' => false
                ]
            )
            ->add('mail', EmailType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'maxlength' => 100
                ]
            ])
            ->add('mail2', EmailType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'maxlength' => 100
                ]
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'maxlength' => 10
                ]
            ])
            ->add('phoneNumber2', TelType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'maxlength' => 10
                ]
            ])
            ->add('contactableMail', CheckboxType::class, [
                'label' => 'Contactable',
                'required' => false
            ])
            ->add('contactableMail2', CheckboxType::class, [
                'label' => 'Contactable',
                'required' => false
            ])
            ->add('contactablePhoneNumber', CheckboxType::class, [
                'label' => 'Contactable',
                'required' => false
            ])
            ->add('contactablePhoneNumber2', CheckboxType::class, [
                'label' => 'Contactable',
                'required' => false
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