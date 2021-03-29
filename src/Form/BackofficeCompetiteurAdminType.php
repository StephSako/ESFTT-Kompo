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

class BackofficeCompetiteurAdminType extends AbstractType
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
            ->add('classementOfficiel', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'min' => 500,
                    'max' => 20000
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('username', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'maxlength' => 50
                ]
            ])
            ->add('isCapitaine', CheckboxType::class, [
                'label' => 'Capitaine',
                'required' => false
            ])
            ->add('isAdmin', CheckboxType::class, [
                'label' => 'Administrateur',
                'required' => false
            ])
            ->add('visitor', CheckboxType::class, [
                'label' => 'Compte visiteur (accès restreints)',
                'required' => false
            ])
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
            ->add('contactableMail', CheckboxType::class, [
                'label' => 'Contactable à cette adresse mail',
                'required' => false
            ])
            ->add('contactableMail2', CheckboxType::class, [
                'label' => 'Contactable à cette adresse mail',
                'required' => false
            ])
            ->add('contactablePhoneNumber', CheckboxType::class, [
                'label' => 'Contactable à ce numéro',
                'required' => false
            ])
            ->add('contactablePhoneNumber2', CheckboxType::class, [
                'label' => 'Contactable à ce numéro',
                'required' => false
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