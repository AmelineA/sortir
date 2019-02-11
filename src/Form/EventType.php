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
            ->add('name',           TextType::class, ['label'=>'Intitulé : '])
            ->add('rdvTime',        DateTimeType::class, ['label'=>'Heure et date du rendez-vous : '])
            ->add('duration',       IntegerType::class, ['label'=>'Durée : '])
            ->add('signOnDeadline', DateTimeType::class, ['label'=>'Date limite d\'inscription : '])
            ->add('maxNumber',      IntegerType::class, ['label'=>'Nombre maximal d\'inscrits : '])
            ->add('description',    TextType::class, ['label'=>'Infos sortie : '])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
