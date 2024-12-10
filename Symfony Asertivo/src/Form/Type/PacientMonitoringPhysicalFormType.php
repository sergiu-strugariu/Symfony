<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\PacientPhysicalData;

class PacientMonitoringPhysicalFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('therapeuticMassage', CheckboxType::class, [
                    'required' => false
                ])
                ->add('tappingMassage', CheckboxType::class, [
                    'required' => false
                ])
                ->add('bodyRepositioningImmobilizedPatients', CheckboxType::class, [
                    'required' => false
                ])
                ->add('correctingBodyPostureAndAlignment', CheckboxType::class, [
                    'required' => false
                ])
                ->add('increasingBodyCoordinationAndBalance', CheckboxType::class, [
                    'required' => false
                ])
                ->add('increasingJointMobilityPassive', CheckboxType::class, [
                    'required' => false
                ])
                ->add('increasingJointMobilityPassiveActive', CheckboxType::class, [
                    'required' => false
                ])
                ->add('increasingJointMobilityActiveVoluntary', CheckboxType::class, [
                    'required' => false
                ])
                ->add('increasingJointMobilityAutoPassive', CheckboxType::class, [
                    'required' => false
                ])
                ->add('increasingMuscleStrengthAndEndurance', CheckboxType::class, [
                    'required' => false
                ])
                ->add('rocherCage', CheckboxType::class, [
                    'required' => false
                ])
                ->add('multifunctionalDevice', CheckboxType::class, [
                    'required' => false
                ])
                ->add('stretching', CheckboxType::class, [
                    'required' => false
                ])
                ->add('walkingExercisesSteps', CheckboxType::class, [
                    'required' => false
                ])
                ->add('walkingExercisesSupport', CheckboxType::class, [
                    'required' => false
                ])
                ->add('walkingExercisesBicycle', CheckboxType::class, [
                    'required' => false
                ])
                ->add('walkingExercisesWalkingLane', CheckboxType::class, [
                    'required' => false
                ])
                ->add('groupExercisesJointMobility', CheckboxType::class, [
                    'required' => false
                ])
                ->add('groupExercisesBodyBalanceAndCoordination', CheckboxType::class, [
                    'required' => false
                ])
                ->add('groupExercisesResistanceAndMuscleStrength', CheckboxType::class, [
                    'required' => false
                ])
                ->add('trellisExercises', CheckboxType::class, [
                    'required' => false
                ])
                ->add('refusal', CheckboxType::class, [
                    'required' => false
                ])
                ->add('medicalProblem', CheckboxType::class, [
                    'required' => false
                ])
            ->add('observations', TextareaType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PacientPhysicalData::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ]);
    }

}
