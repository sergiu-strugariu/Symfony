<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\NursingHomeRoom;

class NursingHomeRoomFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uid', HiddenType::class, [
                'required' => false
            ])
            ->add('roomNumber', TextType::class, [
                'required' => true
            ])
            ->add('floor', TextType::class, [
                'required' => true
            ])
            ->add('numberOfBeds', NumberType::class, [
                'required' => true
            ])
            ->add('details', TextareaType::class, [
                'required' => false
            ])
            ->add('roomType', ChoiceType::class, [
                'required' => true,
                'choices' => NursingHomeRoom::getRoomTypes()
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NursingHomeRoom::class,
            'csrf_protection' => false
        ]);
    }
}
