<?php

namespace App\Form;

use App\Entity\CsvFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserByFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('csvFileName', FileType::class, [
                'label'=>'Importer un fichier CSV',
                'required'=>false,
                'data_class'=>null,
                'empty_data'=> ""
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CsvFile::class,
        ]);
    }
}
