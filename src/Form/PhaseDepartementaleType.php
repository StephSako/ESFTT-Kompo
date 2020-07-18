<?php

namespace App\Form;

use App\Entity\PhaseDepartementale;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhaseDepartementaleType extends AbstractType
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
                    return $competiteur->getPlayersChips($builder->getData()->getIdEquipe());
                },
                'required'   => false,
                'empty_data' => null,
                'label' => false,
                'attr'=> ['class'=>'browser-default'],
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1');

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
                    return $competiteur->getPlayersChips($builder->getData()->getIdEquipe());
                },
                'required'   => false,
                'label' => false,
                'attr'=> ['class'=>'browser-default'],
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1');

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
                    return $competiteur->getPlayersChips($builder->getData()->getIdEquipe());
                },
                'required'   => false,
                'label' => false,
                'attr'=> ['class'=>'browser-default'],
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1');

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
                    return $competiteur->getPlayersChips($builder->getData()->getIdEquipe());
                },
                'label' => false,
                'attr'=> ['class'=>'browser-default'],
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1');

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
            'data_class' => PhaseDepartementale::class,
            'translation_domain' => 'forms'
        ]);
    }
}
