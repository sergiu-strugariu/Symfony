<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\PacientMedicalData;

class PacientMedicalDataFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('bloodType', ChoiceType::class, [
                    'required' => false,
                    'placeholder' => 'Selecteaza o optiune...',
                    'choices' => [
                        'A' => 'A',
                        'B' => 'B',
                        'AB' => 'AB',
                        '0' => '0'
                    ]
                ])
                ->add('rh', ChoiceType::class, [
                    'required' => false,
                    'placeholder' => 'Selecteaza o optiune...',
                    'choices' => [
                        'Pozitiv' => 'Pozitiv',
                        'Negativ' => 'Negativ',
                    ]
                ])
                ->add('vaccinatedAgainstCovid', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('hadCovid', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('alergies', TextareaType::class, [
                    'required' => false
                ])
                ->add('weight', TextareaType::class, [
                    'required' => false
                ])
                ->add('height', TextareaType::class, [
                    'required' => false
                ])
                ->add('admissionDiagnostic', TextareaType::class, [
                    'required' => false
                ])
                ->add('admissionReason', TextareaType::class, [
                    'required' => false
                ])
                ->add('heterolateralCollateralAntecedents', TextareaType::class, [
                    'required' => false
                ])
                ->add('personalPhysiologicalPathologicalAntecedents', TextareaType::class, [
                    'required' => false
                ])
                ->add('livingAndWorkingConditions', TextareaType::class, [
                    'required' => false
                ])
                ->add('behaviors', TextareaType::class, [
                    'required' => false
                ])
                ->add('previousMedication', TextareaType::class, [
                    'required' => false
                ])
                ->add('treatment', TextareaType::class, [
                    'required' => false
                ])
                ->add('epicrisis', TextareaType::class, [
                    'required' => false
                ])
                ->add('surgeryConsultation', TextareaType::class, [
                    'required' => false
                ])
                ->add('radiologicalExaminations', TextareaType::class, [
                    'required' => false
                ])
                ->add('labExaminations', TextareaType::class, [
                    'required' => false
                ])
                ->add('ultrasoundExaminations', TextareaType::class, [
                    'required' => false
                ])
                ->add('otherSpecializedExams', TextareaType::class, [
                    'required' => false
                ])
                ->add('otherTherapeuticProcedures', TextareaType::class, [
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PacientMedicalData::class,
            'csrf_protection' => false
        ]);
    }

}
