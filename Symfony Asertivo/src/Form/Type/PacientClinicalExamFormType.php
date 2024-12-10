<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\PacientClinicalExam;

class PacientClinicalExamFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('objectiveExamination', TextareaType::class, [
                    'required' => false
                ])
                ->add('generalState', TextareaType::class, [
                    'required' => false
                ])
                ->add('nutritionalStatus', TextareaType::class, [
                    'required' => false
                ])
                ->add('stateOfConsciousness', TextareaType::class, [
                    'required' => false
                ])
                ->add('skin', TextareaType::class, [
                    'required' => false
                ])
                ->add('mucous', TextareaType::class, [
                    'required' => false
                ])
                ->add('skinAppendages', TextareaType::class, [
                    'required' => false
                ])
                ->add('connectiveAdiposeTissue', TextareaType::class, [
                    'required' => false
                ])
                ->add('ganglionicSystem', TextareaType::class, [
                    'required' => false
                ])
                ->add('muscularSystem', TextareaType::class, [
                    'required' => false
                ])
                ->add('osteoArticularSystem', TextareaType::class, [
                    'required' => false
                ])
                ->add('respiratoryApparatus', TextareaType::class, [
                    'required' => false
                ])
                ->add('cardiovascularApparatus', TextareaType::class, [
                    'required' => false
                ])
                ->add('digestiveSystem', TextareaType::class, [
                    'required' => false
                ])
                ->add('liverSpleenBileDucts', TextareaType::class, [
                    'required' => false
                ])
                ->add('uroGenitalApparatus', TextareaType::class, [
                    'required' => false
                ])
                ->add('nervousSystemEndocrineSenseOrgans', TextareaType::class, [
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PacientClinicalExam::class,
            'csrf_protection' => false
        ]);
    }

}
