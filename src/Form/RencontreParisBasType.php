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
                    return $competiteur->getPlayersChips($builder->getData()->getIdEquipe());
                },
                'required'   => false,
                'empty_data' => null,
                'label' => false,
                'attr'=> ['class'=>'browser-default'],
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1');

                    switch ($builder->getData()->getIdEquipe()->getIdEquipe()) {
                        case 2:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageParis, '$.1') < 2");
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
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1');

                    switch ($builder->getData()->getIdEquipe()->getIdEquipe()) {
                        case 2:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageParis, '$.1') < 2");
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
                        ->leftJoin('c.disposParis', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->andWhere('d.disponibilite = 1');

                    switch ($builder->getData()->getIdEquipe()->getIdEquipe()) {
                        case 2:
                            $query
                                ->andWhere("JSON_VALUE(c.brulageParis, '$.1') < 2");
                            break;
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
