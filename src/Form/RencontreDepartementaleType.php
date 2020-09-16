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
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1<>'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2<>'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3<>'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4<>'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")");

                    switch ($builder->getData()->getIdEquipe()->getIdEquipe()) {
                        case 2:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2");
                            break;
                        case 3:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2");
                            break;
                        case 4:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.3') < 2");
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
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1<>'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2<>'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3<>'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4<>'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")");

                    switch ($builder->getData()->getIdEquipe()->getIdEquipe()) {
                        case 2:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2");
                            break;
                        case 3:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2");
                            break;
                        case 4:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.3') < 2");
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
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1<>'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2<>'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3<>'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4<>'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")");

                    switch ($builder->getData()->getIdEquipe()->getIdEquipe()) {
                        case 2:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2");
                            break;
                        case 3:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2");
                            break;
                        case 4:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.3') < 2");
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
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1<>'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2<>'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3<>'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")")
                        ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4<>'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $builder->getData()->getIdEquipe()->getIdEquipe() . ")");

                    switch ($builder->getData()->getIdEquipe()->getIdEquipe()) {
                        case 2:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2");
                            break;
                        case 3:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2");
                            break;
                        case 4:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2")
                                ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.3') < 2");
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
