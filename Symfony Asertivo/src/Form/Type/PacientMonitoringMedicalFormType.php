<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\PacientMonitoringMedical;

class PacientMonitoringMedicalFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('temperature', TextType::class, [
                    'required' => false
                ])
                ->add('saturation', TextType::class, [
                    'required' => false
                ])
                ->add('diastolicBloodPressure', TextType::class, [
                    'required' => false
                ])
                ->add('systolicBloodPressure', TextType::class, [
                    'required' => false
                ])
                ->add('heartRate', TextType::class, [
                    'required' => false
                ])
                ->add('glucose', TextType::class, [
                    'required' => false
                ])
                ->add('infusion', TextType::class, [
                    'required' => false
                ])
                ->add('observations', TextareaType::class, [
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PacientMonitoringMedical::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ]);
    }

}
