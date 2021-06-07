<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Repository\DivisionRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeType extends AbstractType
{
    private $dr;

    public function __construct(DivisionRepository $dr){
        $this->dr = $dr;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numero', IntegerType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'validate',
                    'min' => 1,
                    'max' => 100
                ]
            ])
            ->add('idDivision', ChoiceType::class, [
                'attr' => [
                    'class' => 'validate'
                ],
                'label' => false,
                'choices' => $this->getDivisionsOptgroup()
            ])
            ->add('idPoule', EntityType::class, [
                'class' => 'App\Entity\Poule',
                'choice_label' => 'poule',
                'label' => false,
                'empty_data' => null,
                'placeholder' => 'Définir vide',
                'required' => false,
                'query_builder' => function (EntityRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->orderBy('p.poule');
                }
            ]);
    }

    private function getDivisionsOptgroup() : array
    {
        $data = $this->dr->createQueryBuilder('d')
            ->addSelect('c')
            ->leftJoin('d.idChampionnat', 'c')
            ->orderBy('c.nom', 'ASC')
            ->addOrderBy('d.nbJoueurs', 'DESC')
            ->addOrderBy('d.shortName', 'ASC')
            ->getQuery()
            ->getResult();

        $querySorted = [];
        foreach ($data as $item) {
            if (!array_key_exists($item->getIdChampionnat()->getNom(), $querySorted)) $querySorted[$item->getIdChampionnat()->getNom()] = [];
            if ($item->getLongName()) $querySorted[$item->getIdChampionnat()->getNom()][$item->getLongName()] = $item;
        }
        return $querySorted;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
            'translation_domain' => 'forms'
        ]);
    }
}