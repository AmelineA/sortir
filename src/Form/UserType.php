<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('username', TextType::class, [
                'label'=>'Pseudo',
                'attr'=>[
                    'placeholder'=>'ex:yoyo44'
                ],
                'empty_data' => "",
//               ajout d'une contrainte à la place d'une annotation sur l'entity,
//               ce cette manière le username n'est pas obligatoire dans UserByAdminType
                'constraints' => [new NotBlank(['message' => "Veuillez rensigner un identifiant!"])]
            ])
            ->add('name', TextType::class, [
                'label'=>'Nom',
                'attr'=>[
                    'placeholder'=>'Mon nom'
                ],
                'empty_data' => "",
            ])
            ->add('firstName', TextType::class, [
                'label'=>'Prénom',
                'attr'=>[
                    'placeholder'=>'Mon Prénom'
                ],
                'empty_data' => "",
            ])
            ->add('telephone', TextType::class, [
                'label'=>'Téléphone',
                'attr'=>[
                    'placeholder'=>'ex : 0699999999'
                ],
                'empty_data' => "",
            ])
            ->add('email', TextType::class, [
                'label'=>'Email',
                'attr'=>[
                    'placeholder'=>'ex : monEmail@Email.com'
                ],
                'empty_data' => "",
            ])
//            ->add('password', PasswordType::class, [
//                'label'=>'Mot de passe',
//                'attr'=>[
//                    'placeholder'=>'ex: K!4851o$'
//                ],
//                'empty_data' => "",
//                'constraints'=>[new NotBlank(['message'=>'Veuillez renseigner un mot de passe'])]
//            ])
            ->add('profilePictureName', FileType::class, [
                'label'=>"Ma photo",
                'required'=>false,
                'data_class'=>null,
                'attr'=>[
                    'value'=>'choisir une photo'
                ],
                'empty_data' => "",

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
