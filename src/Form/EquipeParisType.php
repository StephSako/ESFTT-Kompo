<?php

namespace App\Form;

use App\Entity\EquipeParis;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeParisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('idDivision', EntityType::class, [
                'class' => 'App\Entity\Division',
                'empty_data' => null,
                'placeholder' => 'Définir vide',
                'required' => false,
                'label' => false,
                'choice_label' => 'longName',
                'query_builder' => function (EntityRepository $dr) {
                    return $dr->createQueryBuilder('d')
                        ->where('d.nbJoueursChampParis IS NOT NULL')
                        ->orderBy('d.idDivision');
                }
            ])
            ->add('idPoule', EntityType::class, [
                'class' => 'App\Entity\Poule',
                'empty_data' => null,
                'label' => false,
                'placeholder' => 'Définir vide',
                'required' => false,
                'choice_label' => 'poule',
                'query_builder' => function (EntityRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->orderBy('p.poule');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EquipeParis::class,
            'translation_domain' => 'forms'
        ]);
    }
}