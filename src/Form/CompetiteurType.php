<?php

namespace App\Form;

use App\Entity\Competiteur;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('dateNaissance', BirthdayType::class, [
                'required' => $options['dateNaissanceRequired'],
                'invalid_message' => 'Cette date de naissance est invalide',
                'label' => false,
                'format' => 'd MMMM y',
            ])
            ->add('imageFile', FileType::class, [
                'label' => false,
                'required' => false
            ]);

        if ($options['adminAccess']) {
            $builder
                ->add('isCapitaine', CheckboxType::class, [
                    'label' => 'Capitaine',
                    'required' => false
                ])
                ->add('isAdmin', CheckboxType::class, [
                    'label' => 'Administrateur',
                    'required' => false
                ])
                ->add('isLoisir', CheckboxType::class, [
                    'label' => 'Loisir',
                    'required' => false
                ])
                ->add('isArchive', CheckboxType::class, [
                    'label' => 'Archivé',
                    'required' => false
                ])
                ->add('isEntraineur', CheckboxType::class, [
                    'label' => 'Entraîneur',
                    'required' => false
                ])
                ->add('isCompetiteur', CheckboxType::class, [
                    'label' => 'Compétiteur',
                    'required' => false
                ])
                ->add('isCritFed', CheckboxType::class, [
                    'label' => 'Critérium fédéral',
                    'required' => false
                ])
                ->add('categorieAge', ChoiceType::class, [
                'label' => false,
                'required' => true,
                'choices' => [
                    'Veteran 5' => 'V5',
                    'Veteran 4' => 'V4',
                    'Veteran 3' => 'V3',
                    'Veteran 2' => 'V2',
                    'Veteran 1' => 'V1',
                    'Senior' => 'S',
                    'Junior 3' => 'J3',
                    'Junior 2' => 'J2',
                    'Junior 1' => 'J1',
                    'Cadet 2' => 'C2',
                    'Cadet 1' => 'C1',
                    'Minime 2' => 'M2',
                    'Minime 1'  =>'M1',
                    'Benjamin 2' => 'B2',
                    'Benjamin 1' => 'B1',
                    'Poussin' => 'P'
                ]
            ]);;
        }

        if ((($options['capitaineAccess'] && $options['adminAccess']) || !$options['capitaineAccess']) && !$options['isArchived']) {
            $builder
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

        if ($options['adminAccess'] && $options['isCertificatInvalid']) {
            $builder->add('anneeCertificatMedical', IntegerType::class, [
                'label' => 'Année certificat médical',
                'required' => false,
                'attr' => [
                    'min' => 2016,
                    'max' => 9999
                ]
            ]);
        }

        if (($options['adminAccess'] || $options['capitaineAccess']) && !$options['isArchived']) {
            $builder
                ->add('licence', IntegerType::class, [
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'maxlength' => 11
                    ]
                ])
                ->add('classementOfficiel', IntegerType::class, [
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'min' => 500,
                        'max' => 20000
                    ]
                ]);
        }

        if ($options['usernameEditable']) {
            $builder->add('username', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'validate',
                    'maxlength' => 50
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Competiteur::class,
            'translation_domain' => 'forms',
            'isCertificatInvalid' => false,
            'capitaineAccess' => false,
            'adminAccess' => false,
            'isArchived' => false,
            'dateNaissanceRequired' => false,
            'usernameEditable' => true
        ]);
    }
}