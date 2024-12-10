<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\PacientFile;

class PacientFileFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('fileName', TextType::class, [
                    'required' => false
                ])
                ->add('fileNumber', TextType::class, [
                    'required' => false
                ])
                ->add('fileDate', DateType::class, [
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => false
                ])
                ->add('fileDetails', TextareaType::class, [
                    'required' => false
                ])
                ->add('uid', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PacientFile::class,
            'csrf_protection' => false
        ]);
    }

}
