<?php

namespace App\Form;

use App\Entity\Competiteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BackofficeCompetiteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => false])
            ->add('classementOfficiel', NumberType::class, ['label' => false, 'required' => false])
            ->add('avatar', UrlType::class, ['label' => false])
            ->add('username', TextType::class, ['label' => false])
            ->add('role', CheckboxType::class, ['label' => 'Capitaine', 'required' => false])
            ->add('visitor', CheckboxType::class, ['label' => 'Visiteur', 'required' => false])
            ->add('mail', EmailType::class, ['label' => false])
            ->add('mail2', EmailType::class, ['label' => false])
            ->add('phone_number', TextType::class, ['label' => false])
            ->add('licence', NumberType::class, ['label' => false, 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Competiteur::class,
            'translation_domain' => 'forms'
        ]);
    }
}