<?php

namespace App\Form;

use App\Entity\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['type_data'] == 'competition') $builder->add('informationsCompetition', HiddenType::class, [
            'required' => false
        ]);
        else if ($options['type_data'] == 'criterium') $builder->add('informationsCriterium', HiddenType::class, [
            'required' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Settings::class,
            'translation_domain' => 'forms',
            'type_data' => ''
        ]);
    }
}