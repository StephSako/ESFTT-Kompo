<?php

namespace App\Form;

use App\Entity\Division;
use App\Entity\EquipeDepartementale;
use App\Entity\Poule;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeDepartementaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('division', EntityType::class, array(
                'class' => 'App\Entity\Division',
                'choice_label' => 'longName',
                'query_builder' => function (EntityRepository $dr) {
                    return $dr->createQueryBuilder('d')
                        ->orderBy('d.idDivision', 'ASC');
                }
            ))
            ->add('poule', EntityType::class, array(
                'class' => 'App\Entity\Poule',
                'choice_label' => 'poule',
                'query_builder' => function (EntityRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->orderBy('p.poule', 'ASC');
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EquipeDepartementale::class,
            'translation_domain' => 'forms'
        ]);
    }
}