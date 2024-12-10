<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\PacientCookFood;

class PacientCookFoodFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('foodOption', ChoiceType::class, [
                    'required' => true,
                    'placeholder' => 'Selecteaza o optiune...',
                    'choices' => PacientCookFood::getOptions()
                ])
                ->add('date', DateType::class, [
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => false
                ])
                ->add('observations', TextareaType::class, [
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PacientCookFood::class,
            'csrf_protection' => false
        ]);
    }

}
