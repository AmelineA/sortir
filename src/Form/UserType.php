<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    protected $em;

    /**
     * UserType constructor.
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $siteRepo = $this->em->getRepository(Site::class);
        $sites = $siteRepo->findAll();

        $builder
            ->add('username', TextType::class, [
                'label'=>'Mon pseudo',
                'attr'=>[
                    'placeholder'=>'ex:yoyo44',
                    'class'=>'form-control col-10'
                ]
            ])
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
        //TODO: gérer l'affichage de site selon si on appelle le form depuis updateMyProfile ou de registerUser
            ->add('site', ChoiceType::class, [
                'choices'=> $sites,
                'label'=>'Site',
                'attr'=>[
                    'class'=>'form-control col-10'
                ]
            ])
            ->add('password', PasswordType::class, [
                'label'=>'Mot de passe',
                'attr'=>[
                    'placeholder'=>'ex: K!4851o$',
                    'class'=>'form-control col-10'
                ]
            ])
            ->add('profilePictureName', FileType::class, [
                'label'=>'Uploader une photo de profil (pas de face)',
                'required'=>false,
                'data_class'=>null
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
