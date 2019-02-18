<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserByAdminType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class, [
                'label'=>'Nom',
                'attr'=>[
                    'placeholder'=>'Mon nom',
                    'class'=>'form-control col-10'
                ]
            ])
            ->add('firstName', TextType::class, [
                'label'=>'Prénom',
                'attr'=>[
                    'placeholder'=>'Mon Prénom',
                    'class'=>'form-control col-10'
                ]
            ])
            ->add('telephone', TextType::class, [
                'label'=>'Téléphone',
                'attr'=>[
                    'placeholder'=>'ex : 0699999999',
                    'class'=>'form-control col-10'
                ]
            ])
            ->add('email', TextType::class, [
                'label'=>'Email',
                'attr'=>[
                    'placeholder'=>'ex : monEmail@Email.com',
                    'class'=>'form-control col-10'
                ]
            ])
            ->add('site', EntityType::class, [
                'label'=>'Site',
                'class' => Site::class,
                'choice_label' => 'name',
                'attr'=>[
                    'class'=>'form-control col-10'
                ]
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
