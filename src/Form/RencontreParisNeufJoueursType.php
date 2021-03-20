<?php

namespace App\Form;

use App\Entity\RencontreParis;
use App\Repository\CompetiteurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RencontreParisNeufJoueursType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('idJoueur1', EntityType::class, [
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'required'   => false,
                'empty_data' => null,
                'placeholder' => 'Définir vide',
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->orderBy('c.nom');
                }
            ])
            ->add('idJoueur2', EntityType::class, [
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'required'   => false,
                'label' => false,
                'placeholder' => 'Définir vide',
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->orderBy('c.nom');
                }
            ])
            ->add('idJoueur3', EntityType::class, [
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'required'   => false,
                'placeholder' => 'Définir vide',
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->orderBy('c.nom');
                }
            ])
            ->add('idJoueur4', EntityType::class, [
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'placeholder' => 'Définir vide',
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->orderBy('c.nom');
                }
            ])
            ->add('idJoueur5', EntityType::class, [
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'placeholder' => 'Définir vide',
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->orderBy('c.nom');
                }
            ])
            ->add('idJoueur6', EntityType::class, [
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'placeholder' => 'Définir vide',
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->orderBy('c.nom');
                }
            ])
            ->add('idJoueur7', EntityType::class, [
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'placeholder' => 'Définir vide',
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->orderBy('c.nom');
                }
            ])
            ->add('idJoueur8', EntityType::class, [
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'placeholder' => 'Définir vide',
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->orderBy('c.nom');
                }
            ])
            ->add('idJoueur9', EntityType::class, [
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'placeholder' => 'Définir vide',
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->orderBy('c.nom');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RencontreParis::class,
            'translation_domain' => 'forms'
        ]);
    }
}
