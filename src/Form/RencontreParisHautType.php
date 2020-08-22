<?php

namespace App\Form;

use App\Entity\RencontreParis;
use App\Repository\CompetiteurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RencontreParisHautType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('idJoueur1', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'required'   => false,
                'empty_data' => null,
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->orderBy('c.nom');
                }
            ))
            ->add('idJoueur2', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'required'   => false,
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->orderBy('c.nom');
                }
            ))
            ->add('idJoueur3', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'required'   => false,
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->orderBy('c.nom');
                }
            ))
            ->add('idJoueur4', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->orderBy('c.nom');
                }
            ))
            ->add('idJoueur5', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->orderBy('c.nom');
                }
            ))
            ->add('idJoueur6', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->orderBy('c.nom');
                }
            ))
            ->add('idJoueur7', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->orderBy('c.nom');
                }
            ))
            ->add('idJoueur8', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->orderBy('c.nom');
                }
            ))
            ->add('idJoueur9', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->orderBy('c.nom');
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RencontreParis::class,
            'translation_domain' => 'forms'
        ]);
    }
}
