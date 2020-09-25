<?php

namespace App\Form;

use App\Entity\JourneeDepartementale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JourneeDepartementaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, [
                'label' => false
            ])
            ->add('undefined', CheckboxType::class, [
                'label' => 'Date indéterminée',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JourneeDepartementale::class,
            'translation_domain' => 'forms'
        ]);
    }
}