<?php

namespace App\Form;

use App\Entity\EquipeDepartementale;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeDepartementaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numero', NumberType::class, [
                'label' => false,
                'required' => true
            ])
            ->add('idDivision', EntityType::class, [
                'class' => 'App\Entity\Division',
                'required' => true,
                'label' => false,
                'choice_label' => 'longName',
                'query_builder' => function (EntityRepository $dr) {
                    return $dr->createQueryBuilder('d')
                        ->where('d.nbJoueursChampDepartementale <> -1')
                        ->orderBy('d.nbJoueursChampDepartementale', 'DESC')
                        ->addOrderBy('d.shortName', 'ASC');
                }
            ])
            ->add('idPoule', EntityType::class, [
                'class' => 'App\Entity\Poule',
                'choice_label' => 'poule',
                'label' => false,
                'empty_data' => null,
                'placeholder' => 'Définir vide',
                'required' => false,
                'query_builder' => function (EntityRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->orderBy('p.poule');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EquipeDepartementale::class,
            'translation_domain' => 'forms'
        ]);
    }
}