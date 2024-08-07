<?php

namespace App\Form;

use App\Entity\Competiteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
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
        if (!$options['isArchived']) {
            if ($options['dateNaissanceRequired'] || $options['adminAccess']) {
                $builder
                    ->add('dateNaissance', BirthdayType::class, [
                        'required' => false,
                        'invalid_message' => 'Cette date de naissance est invalide',
                        'label' => false,
                        'widget' => 'choice',
                        'format' => 'd MMMM y',
                    ]);
            }
            $builder
                ->add('imageFile', FileType::class, [
                    'label' => false,
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
                ]);

            if (!$options['createMode']) {
                $builder
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
        }

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
                ->add('isJeune', CheckboxType::class, [
                    'label' => 'Jeune',
                    'required' => false
                ]);

            if ($options['isCertificatInvalid']) {
                $builder->add('anneeCertificatMedical', IntegerType::class, [
                    'label' => 'Année certificat médical',
                    'required' => false,
                    'attr' => [
                        'min' => 2016,
                        'max' => 9999
                    ]
                ]);
            }

            if (!$options['isArchived']) {
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
                    ->add('licence', TelType::class, [
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
                            'min' => 300,
                            'max' => 40000
                        ]
                    ]);
            }
        }

        if ($options['usernameEditable'] && !$options['createMode'] && !$options['isArchived']) {
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
            'usernameEditable' => true,
            'adminAccess' => false,
            'isArchived' => false,
            'dateNaissanceRequired' => false,
            'createMode' => false
        ]);
    }
}