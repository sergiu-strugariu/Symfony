<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\SummaryPacient;

class SummaryPacientFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('urgent', CheckboxType::class, [
                    'required' => false
                ])
                ->add('observations', TextareaType::class, [
                    'required' => false
                ])
                ->add('status', ChoiceType::class, [
                    'required' => true,
                    'choices' => SummaryPacient::getStatuses()
                ])
                ->add('uid', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => SummaryPacient::class,
            'csrf_protection' => false
        ]);
    }

}
