<?php

namespace App\Form;

use App\Entity\Equipe;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeEditType extends AbstractType
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
            ->add('idDivision', EntityType::class, [
                'class' => 'App\Entity\Division',
                'required' => true,
                'attr' => [
                    'class' => 'validate'
                ],
                'label' => false,
                'choice_label' => 'longName',
                'query_builder' => function (EntityRepository $dr) use ($builder) {
                    return $dr->createQueryBuilder('d')
                        ->where('d.idChampionnat = :idChampionnat')
                        ->setParameter('idChampionnat', $builder->getData()->getIdChampionnat()->getIdChampionnat())
                        ->orderBy('d.nbJoueurs', 'DESC')
                        ->addOrderBy('d.longName', 'ASC')
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
            'data_class' => Equipe::class,
            'translation_domain' => 'forms'
        ]);
    }
}