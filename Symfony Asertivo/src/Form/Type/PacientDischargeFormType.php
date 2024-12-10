<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\PacientDischarge;

class PacientDischargeFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $options['data'];

        $builder
                ->add('dischargeDate', DateType::class, [
                    'required' => true,
                    'widget' => 'single_text',
                    'html5' => false,
                    'data' => $data->getId() !== null ? $data->getDischargeDate() : new \DateTime()
                ])
                ->add('dischargeReason', TextareaType::class, [
                    'required' => $options['require_discharge_reason']
                ])
                ->add('clinicalDiagnostic', TextareaType::class, [
                    'required' => false
                ])
                ->add('paraclinicalDiagnostic', TextareaType::class, [
                    'required' => false
                ])
                ->add('epicrisis', TextareaType::class, [
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PacientDischarge::class,
            'require_discharge_reason' => false,
            'csrf_protection' => false
        ]);

        $resolver->setAllowedTypes('require_discharge_reason', 'bool');
    }

}
