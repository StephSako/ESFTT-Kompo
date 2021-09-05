<?php

namespace App\Form;

use App\Entity\Division;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DivisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shortName', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'validate uppercase',
                    'maxlength' => 2
                ]
            ])
            ->add('longName', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'validate',
                    'maxlength' => 25
                ]
            ])
            ->add('nbJoueurs', IntegerType::class, [
                'empty_data' => 0,
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'validate',
                    'min' => -1,
                    'max' => 9
                ]
            ]);

        if ($options['listChamps']) {
            $builder->add('idChampionnat', ChoiceType::class, [
                'label' => false,
                'required' => true,
                'choices' => $options['listChamps']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Division::class,
            'translation_domain' => 'forms',
            'listChamps' => null
        ]);
    }
}