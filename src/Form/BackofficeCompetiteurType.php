<?php

namespace App\Form;

use App\Entity\Competiteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
            ->add('nom', TextType::class,[
                'label' => 'Nom (NOM Prénom)'])
            ->add('classementOfficiel', NumberType::class)
            ->add('avatar', UrlType::class,[
                'label' => 'Image de profil'])
            ->add('username', TextType::class,[
                'label' => 'Identifiant'])
            ->add('role', CheckboxType::class,[
                'label' => 'Capitaine',
                'required' => false])
            ->add('licence', NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Competiteur::class,
            'translation_domain' => 'forms'
        ]);
    }
}