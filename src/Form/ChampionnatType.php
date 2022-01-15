<?php

namespace App\Form;

use App\Entity\Championnat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChampionnatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'validate',
                    'maxlength' => 50
                ]
            ])
            ->add('limiteBrulage', IntegerType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'max' => 4
                ]
            ])
            ->add('nbJournees', IntegerType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'max' => 10
                ]
            ])
            ->add('j2Rule', CheckboxType::class,[
                'label' => ' ',
                'required' => false
            ])
            ->add('lienFfttApi', ChoiceType::class,[
                'label' => ' ',
                'choices' => Championnat::LINKS_CHAMPIONNATS,
                'required' => false,
                'placeholder' => 'Définir vide'
            ])
            ->add('compoSorted', CheckboxType::class,[
                'label' => ' ',
                'required' => false
            ])
            ->add('periodicite', ChoiceType::class,[
                'label' => ' ',
                'choices' => Championnat::PERIODICITE,
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Championnat::class,
            'translation_domain' => 'forms'
        ]);
    }
}