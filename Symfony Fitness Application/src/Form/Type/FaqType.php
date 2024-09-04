<?php

namespace App\Form\Type;

use App\Entity\Faq;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class FaqType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];
        $translation = $options['translation'];

        $builder
            ->add('question', TextType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getQuestion() : '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('slug', TextType::class, [
                'required' => null === $data->getId() ? false : true,
                'disabled' => null === $data->getId() ? true : false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('answer', TextareaType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getAnswer() : '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $entity = $event->getForm()->getData();

        if (null === $entity->getSlug() && isset($data['question'])) {
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($data['question'])->lower();

            $entity->setSlug($slug);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Faq::class,
            'translation' => null
        ]);
    }
}
