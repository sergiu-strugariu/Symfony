<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\PacientSample;

class PacientSampleFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('description', TextareaType::class, [
                    'required' => true
                ])
                ->add('contactedFamily', CheckboxType::class, [
                    'required' => false
                ])
                ->add('collected', CheckboxType::class, [
                    'required' => false
                ])
                ->add('status', ChoiceType::class, [
                    'required' => true,
                    'choices' => PacientSample::getStatuses()
                ])
                ->add('comment', TextareaType::class, [
                    'required' => true
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PacientSample::class,
            'csrf_protection' => false
        ]);
    }

}
