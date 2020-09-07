<?php

namespace App\Form;

use App\Entity\RencontreParis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BackOfficeRencontreParisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adversaire', TextType::class,[
                'label' => ' '])
            ->add('hosted', CheckboxType::class,[
                'label' => 'Match délocalisé à Herblay',
                'required' => false])
            ->add('domicile', CheckboxType::class,[
                'label' => 'Rencontre à domicile',
                'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RencontreParis::class,
            'translation_domain' => 'forms'
        ]);
    }
}