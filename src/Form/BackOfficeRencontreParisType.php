<?php

namespace App\Form;

use App\Entity\RencontreParis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BackOfficeRencontreParisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adversaire', TextType::class,[
                'label' => false,
                'required' => false,
                'attr' => [
                    'maxlength' => 50,
                    'placeholder' => 'Adversaire + n° équipe'
                ]]
            )
            ->add('hosted', CheckboxType::class,[
                'label' => 'A. Marquet indisponible',
                'required' => false])
            ->add('reporte', CheckboxType::class,[
                'label' => 'Match reporté au',
                'required' => false])
            ->add('dateReport', DateType::class,[
                'label' => false,
                'required' => false])
            ->add('exempt', CheckboxType::class,[
                'label' => 'Pas de match',
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