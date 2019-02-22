<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'=> 'nom du lieu',
                'attr'=>[
                    'placeholder'=>'ex : Le Macoumba',
                ],
                'empty_data'=> ""
            ])
            ->add('street', TextType::class, [
                'label'=> 'adresse',
                'attr'=>[
                    'placeholder'=>'ex : 15 rue de la soif',
                ],
                'empty_data'=> ""
            ])
            ->add('zipCode', TextType::class, [
                'label'=> 'code postal',
                'attr'=>[
                    'placeholder'=>'ex : 44000',
                ],
                'empty_data'=> ""
            ])
            ->add('city', TextType::class, [
                'label'=> 'ville',
                'attr'=>[
                    'placeholder'=>'ex : Nantes',
                ],
                'empty_data'=> ""
            ])
            ->add('latitude', TextType::class, [
                'label'=> 'latitude',
                'attr'=>[
                    'placeholder'=>'+000.46.00',
                ],
                'empty_data'=> ""
            ])
            ->add('longitude', TextType::class, [
                'label'=> 'longitude',
                'attr'=>[
                    'placeholder'=>'-000.05.00',
                ],
                'empty_data'=> ""
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
