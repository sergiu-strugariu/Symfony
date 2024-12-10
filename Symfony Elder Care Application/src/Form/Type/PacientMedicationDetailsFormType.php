<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\PacientMedicationDetails;

class PacientMedicationDetailsFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('status', ChoiceType::class, [
                    'required' => true,
                    'choices' => PacientMedicationDetails::getStatuses()
                ])
                ->add('observations', TextareaType::class, [
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PacientMedicationDetails::class,
            'csrf_protection' => false
        ]);
    }

}
