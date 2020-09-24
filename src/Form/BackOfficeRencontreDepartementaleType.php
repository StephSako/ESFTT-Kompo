<?php

namespace App\Form;

use App\Entity\RencontreDepartementale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BackOfficeRencontreDepartementaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adversaire', TextType::class,[
                'label' => false,
                'attr' => [
                    'placeholder' => 'Adversaire'
                ]]
            )
            ->add('hosted', CheckboxType::class,[
                'label' => 'Salle Albert Marquet indisponible (match à Herblay)',
                'required' => false])
            ->add('reporte', CheckboxType::class,[
                'label' => 'Match reporté au',
                'required' => false])
            ->add('dateReport', DateType::class,[
                'label' => false,
                'required' => false])
            ->add('exempt', CheckboxType::class,[
                'label' => 'Equipe exemptée',
                'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RencontreDepartementale::class,
            'translation_domain' => 'forms'
        ]);
    }
}