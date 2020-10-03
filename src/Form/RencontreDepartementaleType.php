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
                'placeholder' => 'Définir vide',
                'empty_data' => null,
                'label' => false,
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p11.idJoueur1<>'NULL', p11.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p11 WHERE p11.idJournee = d.idJournee AND p11.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p21.idJoueur2<>'NULL', p21.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p21 WHERE p21.idJournee = d.idJournee AND p21.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p31.idJoueur3<>'NULL', p31.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p31 WHERE p31.idJournee = d.idJournee AND p31.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p41.idJoueur4<>'NULL', p41.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p41 WHERE p41.idJournee = d.idJournee AND p41.idEquipe <> :idEquipe)")
                        ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe < :idEquipe) < 2')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe())
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
                'placeholder' => 'Définir vide',
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p11.idJoueur1<>'NULL', p11.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p11 WHERE p11.idJournee = d.idJournee AND p11.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p21.idJoueur2<>'NULL', p21.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p21 WHERE p21.idJournee = d.idJournee AND p21.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p31.idJoueur3<>'NULL', p31.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p31 WHERE p31.idJournee = d.idJournee AND p31.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p41.idJoueur4<>'NULL', p41.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p41 WHERE p41.idJournee = d.idJournee AND p41.idEquipe <> :idEquipe)")
                        ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe < :idEquipe) < 2')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe())
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
                'placeholder' => 'Définir vide',
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p11.idJoueur1<>'NULL', p11.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p11 WHERE p11.idJournee = d.idJournee AND p11.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p21.idJoueur2<>'NULL', p21.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p21 WHERE p21.idJournee = d.idJournee AND p21.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p31.idJoueur3<>'NULL', p31.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p31 WHERE p31.idJournee = d.idJournee AND p31.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p41.idJoueur4<>'NULL', p41.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p41 WHERE p41.idJournee = d.idJournee AND p41.idEquipe <> :idEquipe)")
                        ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe < :idEquipe) < 2')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe())
                        ->orderBy('c.nom');
                }
            ))
            ->add('idJoueur4', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getSelect();
                },
                'label' => false,
                'placeholder' => 'Définir vide',
                'choice_attr' => function ($competiteur) use($builder) {
                    return ['data-icon' => $competiteur->getAvatar()];
                },
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    return $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true')
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p11.idJoueur1<>'NULL', p11.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p11 WHERE p11.idJournee = d.idJournee AND p11.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p21.idJoueur2<>'NULL', p21.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p21 WHERE p21.idJournee = d.idJournee AND p21.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p31.idJoueur3<>'NULL', p31.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p31 WHERE p31.idJournee = d.idJournee AND p31.idEquipe <> :idEquipe)")
                        ->andWhere("c.idCompetiteur NOT IN (SELECT IF(p41.idJoueur4<>'NULL', p41.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p41 WHERE p41.idJournee = d.idJournee AND p41.idEquipe <> :idEquipe)")
                        ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = c.idCompetiteur OR p1.idJoueur2 = c.idCompetiteur OR p1.idJoueur3 = c.idCompetiteur OR p1.idJoueur4 = c.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe < :idEquipe) < 2')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe())
                        ->orderBy('c.nom');
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
