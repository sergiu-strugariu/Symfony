<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\County;
use App\Entity\City;
use App\Entity\Pacient;

class PacientPersonalDataFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('cnp', TextType::class, [
                    'required' => false
                ])
                ->add('firstName', TextType::class, [
                    'required' => false
                ])
                ->add('lastName', TextType::class, [
                    'required' => false
                ])
                ->add('county', EntityType::class, [
                    'required' => false,
                    'class' => County::class,
                    'choice_label' => 'name'
                ])
                ->add('city', EntityType::class, [
                    'required' => false,
                    'class' => City::class,
                    'choice_label' => 'name'
                ])
                ->add('idCardSeries', TextType::class, [
                    'required' => false
                ])
                ->add('idCardNumber', TextType::class, [
                    'required' => false
                ])
                ->add('dateOfBirth', DateType::class, [
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => false
                ])
                ->add('homeAddress', TextareaType::class, [
                    'required' => false
                ])
                ->add('citizenship', TextType::class, [
                    'required' => false
                ])
                ->add('phoneNumber', TextType::class, [
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Pacient::class,
            'csrf_protection' => false
        ]);
    }

}
