<?php

namespace App\Form;

use App\Entity\Equipe;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numero', IntegerType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'validate',
                    'min' => 1,
                    'max' => 100
                ]
            ])
            ->add('idDivision', ChoiceType::class, [
                'attr' => [
                    'class' => 'validate'
                ],
                'required' => false,
                'empty_data' => null,
                'placeholder' => 'Choisissez une division',
                'label' => false,
                'choices' => $options['divisionsOptGroup']
            ])
            ->add('idPoule', EntityType::class, [
                'class' => 'App\Entity\Poule',
                'choice_label' => 'poule',
                'label' => false,
                'empty_data' => null,
                'placeholder' => 'Définir vide',
                'required' => false,
                'query_builder' => function (EntityRepository $pr) {
                    return $pr->createQueryBuilder('p')->orderBy('p.poule');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
            'translation_domain' => 'forms',
            'divisionsOptGroup' => []
        ]);
    }
}