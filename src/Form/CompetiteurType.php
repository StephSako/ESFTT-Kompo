<?php

namespace App\Form;

use App\Entity\Competiteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetiteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => ' '])
            ->add('classementOfficiel', NumberType::class, ['label' => ' '])
            ->add('avatar', UrlType::class, ['label' => ' '])
            ->add('username', TextType::class, ['label' => ' '])
            ->add('licence', NumberType::class, ['label' => ' ']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Competiteur::class,
            'translation_domain' => 'forms'
        ]);
    }
}