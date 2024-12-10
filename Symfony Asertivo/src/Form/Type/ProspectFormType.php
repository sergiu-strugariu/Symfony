<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\Prospect;

class ProspectFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('relationName', TextType::class, [
                    'required' => false
                ])
                ->add('phoneNumber', TextType::class, [
                    'required' => false
                ])
                ->add('email', EmailType::class, [
                    'required' => false
                ])
                ->add('beneficiaryName', TextType::class, [
                    'required' => false
                ])
                ->add('age', NumberType::class, [
                    'required' => false
                ])
                ->add('gender', ChoiceType::class, [
                    'required' => false,
                    'placeholder' => 'Selecteaza o optiune...',
                    'choices' => [
                        'Masculin' => 'Masculin',
                        'Feminin' => 'Feminin'
                    ]
                ])
                ->add('diagnosis', TextareaType::class, [
                    'required' => false
                ])
                ->add('personalNeeds', TextareaType::class, [
                    'required' => false
                ])
                ->add('personality', TextareaType::class, [
                    'required' => false
                ])
                ->add('roomType', ChoiceType::class, [
                    'required' => false,
                    'placeholder' => 'Selecteaza o optiune...',
                    'choices' => [
                        'Single' => 'Single',
                        'Double' => 'Double'
                    ]
                ])
                ->add('currentAddress', TextType::class, [
                    'required' => false
                ])
                ->add('requiresDiapers', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('requiresMedicalBed', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('treatment', TextareaType::class, [
                    'required' => false
                ])
                ->add('estimatedAdmissionDate', DateType::class, [
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => false
                ])
                ->add('observations', TextareaType::class, [
                    'required' => false
                ])
                ->add('scheduledAt', DateTimeType::class, [
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => false
                ])
                ->add('offerSentAt', DateTimeType::class, [
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => false
                ])
                ->add('status', ChoiceType::class, [
                    'required' => true,
                    'placeholder' => 'Selecteaza o optiune...',
                    'choices' => [
                        'In lucru' => 'In lucru',
                        'Lista asteptare' => 'Lista asteptare',
                        'Oferta refuzata' => 'Oferta refuzata',
                        'Oferta acceptata' => 'Oferta acceptata',
                    ]
                ])
                ->add('leadSource', ChoiceType::class, [
                    'required' => false,
                    'placeholder' => 'Selecteaza o optiune...',
                    'choices' => [
                        'Google' => 'Google',
                        'Retele Sociale' => 'Retele Sociale',
                        'Recomandare' => 'Recomandare',
                        'Alta sursa' => 'Alta sursa'
                    ]
                ])
                ->add('leadSourceComment', TextareaType::class, [
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Prospect::class,
            'csrf_protection' => false
        ]);
    }

}
