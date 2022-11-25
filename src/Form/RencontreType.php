<?php

namespace App\Form;

use App\Entity\Rencontre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
        if ($options['editCompoMode']) {
            for($j = 0; $j < $builder->getData()->getIdEquipe()->getIdDivision()->getNbJoueurs(); $j++) {
                $builder->add('idJoueur' . $j, ChoiceType::class, [
                    'choice_label' => function ($competiteur) use ($builder) {
                        return $competiteur->getSelect();
                    },
                    'required' => false,
                    'placeholder' => 'Pas de sélection',
                    'empty_data' => null,
                    'label' => false,
                    'choices' => $options['joueursSelectionnables']
                ]);
            }
        } else {
            $builder
                ->add('domicile', ChoiceType::class,[
                    'label' => ' ',
                    'choices' => Rencontre::LIEU_RENCONTRE,
                    'required' => true
                ])->add('adversaire', TextType::class,[
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'maxlength' => 50
                    ]]
                )->add('reporte', CheckboxType::class,[
                    'label' => 'Match avancé/reporté',
                    'required' => false
                ])->add('dateReport', DateType::class,[
                    'label' => false,
                    'format' => 'd MMMM y'
                ])->add('exempt', CheckboxType::class,[
                    'label' => 'Equipe exemptée',
                    'required' => false
                ])->add('villeHost', TextType::class,[
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'maxlength' => 200,
                        'placeholder' => 'Pas de délocalisation'
                    ]
                ])->add('adresse', TextareaType::class,[
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'maxlength' => 300
                    ]
                ])->add('telephone', TelType::class, [
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'maxlength' => 10
                    ]
                ])->add('site', TextType::class,[
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'maxlength' => 100
                    ]
                ])->add('complementAdresse', TextareaType::class,[
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'maxlength' => 300
                    ]
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rencontre::class,
            'translation_domain' => 'forms',
            'editCompoMode' => null,
            'joueursSelectionnables' => []
        ]);
    }
}
