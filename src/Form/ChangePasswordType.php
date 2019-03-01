<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'label'=>'Ancien mot de passe'
            ])
            ->add('newPassword', RepeatedType::class, [
                'type'=> PasswordType::class,
                'invalid_message'=>'Les champs doivent être identiques',
                'required'=> true,
                'first_options' => ['label'=>'Nouveau mot de passe'],
                'second_options' => ['label'=>'Confirmation nouveau mot de passe'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
