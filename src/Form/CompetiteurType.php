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
            ->add('nom', TextType::class,[
                'attr' => ['data-length' => 60]])
            ->add('avatar', UrlType::class,[
                'label' => 'Image de profil',
                'attr' => ['data-length' => 60]])
            ->add('username', TextType::class,[
                'label' => 'Identifiant',
                'attr' => ['data-length' => 50]])
            ->add('licence', NumberType::class,[
                'attr' => ['data-length' => 11]]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Competiteur::class,
            'translation_domain' => 'forms',
            'validation_groups' => ['edit', 'registration']
        ]);
    }
}