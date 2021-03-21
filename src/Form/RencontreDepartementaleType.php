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
        for($j = 1; $j <= $builder->getData()->getIdEquipe()->getIdDivision()->getNbJoueursChampDepartementale(); $j++) {
            $builder->add('idJoueur' . $j, EntityType::class, [
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use ($builder) {
                    return $competiteur->getSelect();
                },
                'required' => false,
                'placeholder' => 'Définir vide',
                'empty_data' => null,
                'label' => false,
                'choice_attr' => function ($competiteur) use ($builder) {
                    return ['data-icon' => '/images/profile_pictures/' . $competiteur->getAvatar()];
                },
                'query_builder' => function (CompetiteurRepository $cr) use ($options, $builder) {
                    $request = $cr->createQueryBuilder('c')
                        ->leftJoin('c.disposDepartementales', 'd')
                        ->where('d.idJournee = :idJournee')
                        ->andWhere('d.disponibilite = 1')
                        ->andWhere('c.visitor <> true');
                    $str = '';
                    for ($i = 1; $i <= intval($options['nbMaxJoueursUsed']); $i++) {
                        $str .= 'p.idJoueur' . $i . ' = c.idCompetiteur';
                        if ($i < intval($options['nbMaxJoueursUsed'])) $str .= ' OR ';
                        $request->andWhere("c.idCompetiteur NOT IN (SELECT IF(p" . $i . ".idJoueur" . $i . " <> 'NULL', p" . $i . ".idJoueur" . $i . ", 0) FROM App\Entity\RencontreDepartementale p" . $i . " WHERE p" . $i . ".idJournee = d.idJournee AND p" . $i . ".idEquipe <> :idEquipe)");
                    }
                    $request->andWhere('(SELECT COUNT(p.id) FROM App\Entity\RencontreDepartementale p WHERE (' .$str . ') AND p.idJournee < :idJournee AND p.idEquipe < :idEquipe) < ' . $options['limiteBrulage'])
                        ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                        ->setParameter('idEquipe', $builder->getData()->getIdEquipe()->getIdEquipe())
                        ->orderBy('c.nom');
                    return $request;
            }
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RencontreDepartementale::class,
            'translation_domain' => 'forms',
            'nbMaxJoueursUsed' => null,
            'limiteBrulage' => null
        ]);
    }
}
