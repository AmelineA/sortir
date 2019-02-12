<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('site',   TextType::class, ['label'=>'Site : ', 'attr'=>['class'=>'form-control']])
            ->add('searchBar', TextType::class, ['label'=>'Le nom de la sortie contient : '])
            ->add('dateStart', DateTimeType::class, ['label'=>'Entre : ']   )
            ->add('dateEnd', DateTimeType::class, ['label'=>' et : ']   )
            ->add('organizer', CheckboxType::class, ['label'=>' Sorties dont je suis l\'organisateur/trice'])
            ->add('signedOn',CheckboxType::class, ['label'=>' Sorties auxquelles je suis inscrit/e'])
            ->add('notSignedOn',CheckboxType::class, ['label'=>' Sorties auxquelles je ne suis pas inscrit/e'])
            ->add('pastEvent',CheckboxType::class, ['label'=>' Sorties passées'])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
