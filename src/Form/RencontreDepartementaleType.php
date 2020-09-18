<?php

namespace App\Form;

use App\Entity\RencontreDepartementale;
use App\Repository\CompetiteurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RencontreDepartementaleType extends AbstractType
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
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p11.idJoueur1<>'NULL', p11.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p11 WHERE p11.idJournee = d.idJournee AND p11.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p21.idJoueur2<>'NULL', p21.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p21 WHERE p21.idJournee = d.idJournee AND p21.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p31.idJoueur3<>'NULL', p31.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p31 WHERE p31.idJournee = d.idJournee AND p31.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p41.idJoueur4<>'NULL', p41.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p41 WHERE p41.idJournee = d.idJournee AND p41.idEquipe <> :idEquipe)")
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe());

                    switch ($builder->getData()->getIdEquipe()->getIdEquipe()) {
                        case 2:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                        case 3:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->andWhere('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = c.idCompetiteur OR p2.idJoueur2 = c.idCompetiteur OR p2.idJoueur3 = c.idCompetiteur OR p2.idJoueur4 = c.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                        case 4:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->andWhere('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = c.idCompetiteur OR p2.idJoueur2 = c.idCompetiteur OR p2.idJoueur3 = c.idCompetiteur OR p2.idJoueur4 = c.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) < 2')
                                ->andWhere('(SELECT COUNT(p3.id) FROM App\Entity\RencontreDepartementale p3 WHERE (p3.idJoueur1 = c.idCompetiteur OR p3.idJoueur2 = c.idCompetiteur OR p3.idJoueur3 = c.idCompetiteur OR p3.idJoueur4 = c.idCompetiteur) AND p3.idJournee < :idJournee AND p3.idEquipe = 3) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                    }
                    return $query->orderBy('c.nom');
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
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p12.idJoueur1<>'NULL', p12.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p12 WHERE p12.idJournee = d.idJournee AND p12.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p22.idJoueur2<>'NULL', p22.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p22 WHERE p22.idJournee = d.idJournee AND p22.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p32.idJoueur3<>'NULL', p32.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p32 WHERE p32.idJournee = d.idJournee AND p32.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p42.idJoueur4<>'NULL', p42.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p42 WHERE p42.idJournee = d.idJournee AND p42.idEquipe <> :idEquipe)")
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe());

                    switch ($builder->getData()->getIdEquipe()->getIdEquipe()) {
                        case 2:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                        case 3:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->andWhere('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = c.idCompetiteur OR p2.idJoueur2 = c.idCompetiteur OR p2.idJoueur3 = c.idCompetiteur OR p2.idJoueur4 = c.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                        case 4:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->andWhere('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = c.idCompetiteur OR p2.idJoueur2 = c.idCompetiteur OR p2.idJoueur3 = c.idCompetiteur OR p2.idJoueur4 = c.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) < 2')
                                ->andWhere('(SELECT COUNT(p3.id) FROM App\Entity\RencontreDepartementale p3 WHERE (p3.idJoueur1 = c.idCompetiteur OR p3.idJoueur2 = c.idCompetiteur OR p3.idJoueur3 = c.idCompetiteur OR p3.idJoueur4 = c.idCompetiteur) AND p3.idJournee < :idJournee AND p3.idEquipe = 3) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                    }
                    return $query->orderBy('c.nom');
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
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p13.idJoueur1<>'NULL', p13.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p13 WHERE p13.idJournee = d.idJournee AND p13.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p23.idJoueur2<>'NULL', p23.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p23 WHERE p23.idJournee = d.idJournee AND p23.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p33.idJoueur3<>'NULL', p33.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p33 WHERE p33.idJournee = d.idJournee AND p33.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p43.idJoueur4<>'NULL', p43.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p43 WHERE p43.idJournee = d.idJournee AND p43.idEquipe <> :idEquipe)")
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe());

                    switch ($builder->getData()->getIdEquipe()->getIdEquipe()) {
                        case 2:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                        case 3:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->andWhere('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = c.idCompetiteur OR p2.idJoueur2 = c.idCompetiteur OR p2.idJoueur3 = c.idCompetiteur OR p2.idJoueur4 = c.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                        case 4:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->andWhere('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = c.idCompetiteur OR p2.idJoueur2 = c.idCompetiteur OR p2.idJoueur3 = c.idCompetiteur OR p2.idJoueur4 = c.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) < 2')
                                ->andWhere('(SELECT COUNT(p3.id) FROM App\Entity\RencontreDepartementale p3 WHERE (p3.idJoueur1 = c.idCompetiteur OR p3.idJoueur2 = c.idCompetiteur OR p3.idJoueur3 = c.idCompetiteur OR p3.idJoueur4 = c.idCompetiteur) AND p3.idJournee < :idJournee AND p3.idEquipe = 3) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                    }
                    return $query->orderBy('c.nom');
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
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p14.idJoueur1<>'NULL', p14.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p14 WHERE p14.idJournee = d.idJournee AND p14.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p24.idJoueur2<>'NULL', p24.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p24 WHERE p24.idJournee = d.idJournee AND p24.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p34.idJoueur3<>'NULL', p34.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p34 WHERE p34.idJournee = d.idJournee AND p34.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p44.idJoueur4<>'NULL', p44.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p44 WHERE p44.idJournee = d.idJournee AND p44.idEquipe <> :idEquipe)")
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe());

                    switch ($builder->getData()->getIdEquipe()->getIdEquipe()) {
                        case 2:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                        case 3:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->andWhere('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = c.idCompetiteur OR p2.idJoueur2 = c.idCompetiteur OR p2.idJoueur3 = c.idCompetiteur OR p2.idJoueur4 = c.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                        case 4:
                            $query
                                ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                                ->andWhere('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = c.idCompetiteur OR p2.idJoueur2 = c.idCompetiteur OR p2.idJoueur3 = c.idCompetiteur OR p2.idJoueur4 = c.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) < 2')
                                ->andWhere('(SELECT COUNT(p3.id) FROM App\Entity\RencontreDepartementale p3 WHERE (p3.idJoueur1 = c.idCompetiteur OR p3.idJoueur2 = c.idCompetiteur OR p3.idJoueur3 = c.idCompetiteur OR p3.idJoueur4 = c.idCompetiteur) AND p3.idJournee < :idJournee AND p3.idEquipe = 3) < 2')
                                ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee());
                            break;
                    }
                    return $query->orderBy('c.nom');
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RencontreDepartementale::class,
            'translation_domain' => 'forms'
        ]);
    }
}
