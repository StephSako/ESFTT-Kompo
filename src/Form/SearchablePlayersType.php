<?php

namespace App\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchablePlayersType extends AbstractType
{

    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('class')
            ->setDefaults([
                'compound' => false,
                'multiple' => true,
                'choices' => []
            ]);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @return void
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['expanded'] = false;
        $view->vars['placeholder'] = null;
        $view->vars['placeholder_in_choices'] = false;
        $view->vars['multiple'] = true;
        $view->vars['preferred_choices'] = [];
        $view->vars['choices'] = $this->choices(new ArrayCollection($options['choices']));
        $view->vars['choice_translation_domain'] = false;
        $view->vars['full_name'] .= '[]';
    }

    /**
     * @param Collection $competiteurs
     * @return array
     */
    private function choices(Collection $competiteurs): array
    {
        return array_map(function ($c) {
            return new ChoiceView($c, (string)$c->getIdCompetiteur(), $c->getNom() . ' ' . $c->getPrenom());
        }, $competiteurs->toArray());
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'choice';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function (Collection $values): array {
                return array_map(function ($c) {
                    return (string)$c->getIdCompetiteur();
                }, $values->toArray());
            }, function (?array $ids = []) use ($options): Collection {
            if (empty($ids)) return new ArrayCollection([]);
            return new ArrayCollection(
                $this->em->getRepository($options['class'])->findBy(['idCompetiteur' => $ids])
            );
        }
        ));
    }
}