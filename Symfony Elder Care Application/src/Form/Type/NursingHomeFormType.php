<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\NursingHome;
use App\Entity\County;
use App\Entity\City;
use Symfony\Component\Validator\Constraints as Assert;

class NursingHomeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Acest câmp este obligatoriu.'
                    ])
                ]
            ])
            ->add('officialName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Acest câmp este obligatoriu.'
                    ])
                ]
            ])
            ->add('dpoEmail', EmailType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Email([
                        'message' => 'Formatul adresei de email este invalid.'
                    ])
                ]
            ])
            ->add('address', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Acest câmp este obligatoriu.'
                    ])
                ]
            ])
            ->add('county', EntityType::class, [
                'required' => true,
                'class' => County::class,
                'choice_label' => 'name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Acest câmp este obligatoriu.'
                    ])
                ]
            ])
            ->add('city', EntityType::class, [
                'required' => true,
                'class' => City::class,
                'choice_label' => 'name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Acest câmp este obligatoriu.'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NursingHome::class
        ]);
    }
}
