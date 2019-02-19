<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('username', TextType::class, [
                'label'=>'Pseudo',
                'attr'=>[
                    'placeholder'=>'ex:yoyo44',
                    'class'=>'form-control col-10'
                ],
                'empty_data'=> ""
            ])
            ->add('name', TextType::class, [
                'label'=>'Nom',
                'attr'=>[
                    'placeholder'=>'Mon nom',
                    'class'=>'form-control col-10'
                ],
                'empty_data'=> ""
            ])
            ->add('firstName', TextType::class, [
                'label'=>'Prénom',
                'attr'=>[
                    'placeholder'=>'Mon Prénom',
                    'class'=>'form-control col-10'
                ],
                'empty_data'=> ""
            ])
            ->add('telephone', TextType::class, [
                'label'=>'Téléphone',
                'attr'=>[
                    'placeholder'=>'ex : 0699999999',
                    'class'=>'form-control col-10'
                ],
                'empty_data'=> ""
            ])
            ->add('email', TextType::class, [
                'label'=>'Email',
                'attr'=>[
                    'placeholder'=>'ex : monEmail@Email.com',
                    'class'=>'form-control col-10'
                ],
                'empty_data'=> ""
            ])
            ->add('password', PasswordType::class, [
                'label'=>'Mot de passe',
                'attr'=>[
                    'placeholder'=>'ex: K!4851o$',
                    'class'=>'form-control col-10'
                ],
                'empty_data' => "",
            ])
            ->add('profilePictureName', FileType::class, [
                'label'=>"Ma photo",
                'required'=>false,
                'data_class'=>null,
                'attr'=>[
                    'class'=> 'form-control col-10',
                    'value'=>'choisir une photo'
                ],
                'empty_data'=> ""

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
