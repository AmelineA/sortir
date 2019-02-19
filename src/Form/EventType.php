<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;

class EventType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',           TextType::class, [
                'label'=>'La sortie',
                'empty_data'=> ""
            ])
            ->add('rdvTime',        DateTimeType::class, [
                'label'=>'Le rendez-vous',
                'widget' => 'single_text',
                'data'=>new \DateTime('now')
            ])
            ->add('duration',       IntegerType::class, [
                'label'=>'Durée',
                'empty_data'=> ""
            ])
            ->add('signOnDeadline', DateTimeType::class, [
                'label'=>'Date limite d\'inscription',
                'widget' => 'single_text',
                'date_format' => "Y",
                'data'=>new \DateTime('now')
            ])
            ->add('maxNumber',      IntegerType::class, [
                'label'=>'Nombre maximal d\'inscrits',
                'empty_data'=> ""
            ])
            ->add('description',    TextType::class, [
                'label'=>'Infos sortie',
                'empty_data'=> ""
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
