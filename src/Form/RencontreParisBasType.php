<?php

namespace App\Form;

use App\Entity\RencontreParis;
use App\Repository\CompetiteurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RencontreParisBasType extends AbstractType
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
                'placeholder' => 'Définir vide',
                'empty_data' => null,
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> :idEquipe)")
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe());

                    if ($builder->getData()->getIdEquipe()->getIdEquipe() == 2) {
                            $query->andWhere("(SELECT COUNT(p.id) FROM App\Entity\RencontreParis p WHERE (p.idJoueur1 = c.idCompetiteur OR p.idJoueur2 = c.idCompetiteur OR p.idJoueur3 = c.idCompetiteur OR p.idJoueur4 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur6 = c.idCompetiteur OR p.idJoueur7 = c.idCompetiteur OR p.idJoueur8 = c.idCompetiteur OR p.idJoueur9 = c.idCompetiteur) AND p.idJournee < :idJournee AND p.idEquipe = 1) < 3")
                                  ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                    }
                    return $query->orderBy('c.nom');
                }
            ))
            ->add('idJoueur2', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'placeholder' => 'Définir vide',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'required'   => false,
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> :idEquipe)")
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe());

                    if ($builder->getData()->getIdEquipe()->getIdEquipe() == 2) {
                        $query->andWhere("(SELECT COUNT(p.id) FROM App\Entity\RencontreParis p WHERE (p.idJoueur1 = c.idCompetiteur OR p.idJoueur2 = c.idCompetiteur OR p.idJoueur3 = c.idCompetiteur OR p.idJoueur4 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur6 = c.idCompetiteur OR p.idJoueur7 = c.idCompetiteur OR p.idJoueur8 = c.idCompetiteur OR p.idJoueur9 = c.idCompetiteur) AND p.idJournee < :idJournee AND p.idEquipe = 1) < 3")
                              ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                    }
                    return $query->orderBy('c.nom');
                }
            ))
            ->add('idJoueur3', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'placeholder' => 'Définir vide',
                'required'   => false,
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> :idEquipe)")
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe());

                    if ($builder->getData()->getIdEquipe()->getIdEquipe() == 2) {
                        $query->andWhere("(SELECT COUNT(p.id) FROM App\Entity\RencontreParis p WHERE (p.idJoueur1 = c.idCompetiteur OR p.idJoueur2 = c.idCompetiteur OR p.idJoueur3 = c.idCompetiteur OR p.idJoueur4 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur5 = c.idCompetiteur OR p.idJoueur6 = c.idCompetiteur OR p.idJoueur7 = c.idCompetiteur OR p.idJoueur8 = c.idCompetiteur OR p.idJoueur9 = c.idCompetiteur) AND p.idJournee < :idJournee AND p.idEquipe = 1) < 3")
                              ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                    }
                    return $query->orderBy('c.nom');
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
