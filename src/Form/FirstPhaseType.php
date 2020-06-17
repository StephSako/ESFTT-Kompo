<?php

namespace App\Form;

use App\Entity\FirstPhase;
use App\Repository\CompetiteurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FirstPhaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('idJoueur1', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => 'nom',
                'query_builder' => function (CompetiteurRepository $cr) {
                    return $cr->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                }
            ))
            ->add('idJoueur2', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => 'nom',
                'query_builder' => function (CompetiteurRepository $cr) {
                    return $cr->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                }
            ))
            ->add('idJoueur3', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => 'nom',
                'query_builder' => function (CompetiteurRepository $cr) {
                    return $cr->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                }
            ))
            ->add('idJoueur4', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => 'nom',
                'query_builder' => function (CompetiteurRepository $cr) {
                    return $cr->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FirstPhase::class,
            'translation_domain' => 'forms'
        ]);
    }
}
