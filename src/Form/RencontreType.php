<?php

namespace App\Form;

use App\Entity\Rencontre;
use App\Repository\CompetiteurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RencontreType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['nbMaxJoueurs'] && $options['limiteBrulage']) {
            for($j = 0; $j < $builder->getData()->getIdEquipe()->getIdDivision()->getNbJoueurs(); $j++) {
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
                        return ['data-icon' => $competiteur->getAvatar() ? '/images/profile_pictures/' . $competiteur->getAvatar() : '/images/account.png'];
                    },
                    'query_builder' => function (CompetiteurRepository $cr) use ($j, $options, $builder) {
                        $request = $cr->createQueryBuilder('c')
                            ->leftJoin('c.dispos', 'd')
                            ->where('d.idJournee = :idJournee')
                            ->andWhere('d.disponibilite = 1')
                            ->andWhere('d.idChampionnat = :idChampionnat')
                            ->andWhere('c.isCompetiteur = true');
                        $str = '';
                        for ($i = 0; $i < $options['nbMaxJoueurs']; $i++) {
                            $str .= 'p.idJoueur' . $i . ' = c.idCompetiteur';
                            if ($i < $options['nbMaxJoueurs'] - 1) $str .= ' OR ';
                            $request = $request
                                ->andWhere('c.idCompetiteur NOT IN (SELECT IF(p' . $i . '.idJoueur' . $i . ' IS NOT NULL, p' . $i . '.idJoueur' . $i . ', 0) ' .
                                                                    ' FROM App\Entity\Rencontre p' . $i . ', App\Entity\Equipe e' . $i .'e' .
                                                                    ' WHERE p' . $i . '.idJournee = d.idJournee' .
                                                                    ' AND p' . $i . '.idEquipe = e' . $i .'e.idEquipe'.
                                                                    ' AND e' . $i .'e.numero <> :numero'.
                                                                    ' AND p' . $i . '.idChampionnat = :idChampionnat)');
                        }
                        return $request
                            ->andWhere('(SELECT COUNT(p.id) FROM App\Entity\Rencontre p, App\Entity\Equipe eBis' .
                                       ' WHERE (' . $str . ')' .
                                       ' AND p.idJournee < :idJournee' .
                                       ' AND p.idEquipe = eBis.idEquipe' .
                                       ' AND eBis.numero < :numero ' .
                                       ' AND p.idChampionnat = :idChampionnat) < ' . $options['limiteBrulage'])
                            ->setParameter('idJournee', $builder->getData()->getIdJournee()->getIdJournee())
                            ->setParameter('numero', $builder->getData()->getIdEquipe()->getNumero())
                            ->setParameter('idChampionnat', $builder->getData()->getIdChampionnat()->getIdChampionnat())
                            ->orderBy('c.nom')
                            ->addOrderBy('c.prenom');
                    }
                ]);
            }
        } else {
            $builder
                ->add('adversaire', TextType::class,[
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'maxlength' => 50,
                        'placeholder' => 'Adversaire + n° équipe'
                    ]]
                )
                ->add('hosted', CheckboxType::class,[
                    'label' => 'Salle hôte indisponible',
                    'required' => false
                ])
                ->add('reporte', CheckboxType::class,[
                    'label' => 'Match avancé/reporté',
                    'required' => false
                ])
                ->add('dateReport', DateType::class,[
                    'label' => false,
                    'format' => 'd MMMM y'
                ])
                ->add('exempt', CheckboxType::class,[
                    'label' => 'Equipe exemptée',
                    'required' => false
                ])
                ->add('villeHost', TextType::class,[
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'maxlength' => 50,
                        'placeholder' => 'Ville hôte'
                    ]
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rencontre::class,
            'translation_domain' => 'forms',
            'nbMaxJoueurs' => null,
            'limiteBrulage' => null
        ]);
    }
}
