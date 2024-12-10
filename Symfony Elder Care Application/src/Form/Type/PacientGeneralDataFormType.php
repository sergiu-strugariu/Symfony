<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\PacientGeneralData;

class PacientGeneralDataFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $options['data'];
        
        $builder
                ->add('pacientType', ChoiceType::class, [
                    'required' => $options['require_all_fields'],
                    'placeholder' => 'Selecteaza o optiune...',
                    'choices' => [
                        'Dependent' => 'Dependent',
                        'Semidependent' => 'Semidependent',
                        'Independent' => 'Independent'
                    ]
                ])
                ->add('pacientMobility', ChoiceType::class, [
                    'required' => $options['require_all_fields'],
                    'placeholder' => 'Selecteaza o optiune...',
                    'choices' => [
                        'Deplasabil' => 'Deplasabil',
                        'Nedeplasabil' => 'Nedeplasabil',
                        'Cu ajutor' => 'Cu ajutor'
                    ]
                ])
                ->add('diet', ChoiceType::class, [
                    'required' => $options['require_all_fields'],
                    'placeholder' => 'Selecteaza o optiune...',
                    'choices' => [
                        'Normal' => 'Normal',
                        'Vegetarian' => 'Vegetarian',
                        'Diabetic' => 'Diabetic',
                        'Pasat' => 'Pasat',
                        'Fara sare' => 'Hiposodat'
                    ]
                ])
                ->add('requiresDiapers', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('requiresMedicalBed', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('admissionDate', DateType::class, [
                    'required' => true,
                    'widget' => 'single_text',
                    'html5' => false,
                    'data' => $data->getId() !== null ? $data->getAdmissionDate() : new \DateTime()
                ])
                ->add('nosocomialInfection', TextareaType::class, [
                    'required' => $options['require_all_fields']
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PacientGeneralData::class,
            'require_all_fields' => false,
            'csrf_protection' => false
        ]);

        $resolver->setAllowedTypes('require_all_fields', 'bool');
    }

}
