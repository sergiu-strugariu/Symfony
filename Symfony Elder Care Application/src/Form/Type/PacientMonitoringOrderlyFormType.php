<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\PacientOrderlyData;

class PacientMonitoringOrderlyFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('hydrationFood', CheckboxType::class, [
                    'required' => false
                ])
                ->add('diuresis', CheckboxType::class, [
                    'required' => false
                ])
                ->add('stool', CheckboxType::class, [
                    'required' => false
                ])
                ->add('bathing', CheckboxType::class, [
                    'required' => false
                ])
                ->add('diapers', CheckboxType::class, [
                    'required' => false
                ])
                ->add('date', DateTimeType::class, [
                    'required' => true,
                    'widget' => 'single_text',
                    'html5' => false
                ])
                ->add('observations', TextareaType::class, [
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PacientOrderlyData::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ]);
    }

}
