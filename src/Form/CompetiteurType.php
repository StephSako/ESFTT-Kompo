<?php

namespace App\Form;

use App\Entity\Competiteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetiteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => false])
            ->add('classementOfficiel', NumberType::class, ['label' => false])
            ->add('imageFile', FileType::class, ['label' => false, 'required' => false])
            ->add('mail', EmailType::class, ['label' => false, 'required' => false])
            ->add('mail2', EmailType::class, ['label' => false, 'required' => false])
            ->add('phoneNumber', TextType::class, ['label' => false, 'required' => false])
            ->add('phoneNumber2', TextType::class, ['label' => false, 'required' => false])
            ->add('contactableMail', CheckboxType::class, ['label' => 'Contactable à cette adresse mail', 'required' => false])
            ->add('contactableMail2', CheckboxType::class, ['label' => 'Contactable à cette adresse mail', 'required' => false])
            ->add('contactablePhoneNumber', CheckboxType::class, ['label' => 'Contactable à ce numéro', 'required' => false])
            ->add('contactablePhoneNumber2', CheckboxType::class, ['label' => 'Contactable à ce numéro', 'required' => false])
            ->add('username', TextType::class, ['label' => false])
            ->add('licence', NumberType::class, ['label' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Competiteur::class,
            'translation_domain' => 'forms'
        ]);
    }
}