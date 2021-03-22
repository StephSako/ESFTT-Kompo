<?php

namespace App\Form;

use App\Entity\Division;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DivisionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shortName', TextType::class, ['label' => false, 'required' => true])
            ->add('longName', TextType::class, ['label' => false, 'required' => true])
            ->add('nbJoueursChampParis', NumberType::class, ['invalid_message' => 'Indiquez 0 s\'il n\'y a pas cette division en champ. de Paris', 'empty_data' => 0, 'label' => false, 'required' => false])
            ->add('nbJoueursChampDepartementale', NumberType::class, ['invalid_message' => 'Indiquez 0 s\'il n\'y a pas cette division en champ. depart.', 'empty_data' => 0, 'label' => false, 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Division::class,
            'translation_domain' => 'forms'
        ]);
    }
}