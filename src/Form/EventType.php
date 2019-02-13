<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',           TextType::class, ['label'=>'La sortie', 'attr'=>['class'=>'form-control col-10']])
            ->add('rdvTime',        DateTimeType::class, ['label'=>'Le rendez-vous', 'attr'=>['class'=>'form-control col-10 ']])
            ->add('duration',       IntegerType::class, ['label'=>'Durée', 'attr'=>['class'=>'form-control col-10']])
            ->add('signOnDeadline', DateTimeType::class, ['label'=>'Date limite d\'inscription', 'attr'=>['class'=>'form-control col-10']])
            ->add('maxNumber',      IntegerType::class, ['label'=>'Nombre maximal d\'inscrits', 'attr'=>['class'=>'form-control col-10']])
            ->add('description',    TextType::class, ['label'=>'Infos sortie', 'attr'=>['class'=>'form-control col-10']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
