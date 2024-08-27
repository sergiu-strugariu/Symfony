<?php

namespace App\Form\Type;

use App\Entity\Article;
use App\Entity\City;
use App\Entity\County;
use App\Entity\Gallery;
use App\Repository\CityRepository;
use App\Repository\CountyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

class GalleryType extends AbstractType
{
    protected CityRepository $cityRepository;
    protected CountyRepository $countyRepository;

    /**
     * @param CityRepository $cityRepository
     * @param CountyRepository $countyRepository
     */
    public function __construct(CityRepository $cityRepository, CountyRepository $countyRepository)
    {
        $this->cityRepository = $cityRepository;
        $this->countyRepository = $countyRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];
        
        $imageConstraints = [
            new Assert\File([
                'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
                'mimeTypesMessage' => 'common.not_blank',
                'maxSize' => '3M'
            ])
        ];
        if (null === $data->getId()) {
            $additionalImageConstraints = [
                new Assert\NotBlank([
                    'message' => 'common.not_blank'
                ])
            ];
            $imageConstraints = array_merge($imageConstraints, $additionalImageConstraints);
        } 
        
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('eventDate', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'html5' => false,
                'data' => new \DateTime(),
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('image', FileType::class, [
                'required' => !$data->getId(),
                'mapped' => false,
                'constraints' => $imageConstraints
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Convention' => Gallery::TYPE_CONVENTION,
                    'Workshop' => Gallery::TYPE_WORKSHOP,
                    'Course' => Gallery::TYPE_COURSE,
                ],
            ])
            ->add('galleryLink', UrlType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('county', EntityType::class, [
                'class' => County::class,
                'required' => true,
                'placeholder' => 'Choose a county',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.id', 'ASC');
                },
                'choice_label' => 'name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('status', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Published' => Gallery::STATUS_PUBLISHED,
                    'Draft' => Gallery::STATUS_DRAFT
                ]
            ]);

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @param FormInterface $form
     * @param County|null $county
     * @return void
     */
    protected function addElements(FormInterface $form, County $county = null): void
    {
        $cities = $this->cityRepository->findBy(['county' => $county], ['name' => 'ASC']);

        $form->add('city', EntityType::class, [
            'required' => true,
            'class' => City::class,
            'choices' => $cities,
            'placeholder' => 'Choose a city',
            'choice_label' => 'name',
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'common.not_blank'
                ])
            ]
        ]);
    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        $entity = $event->getForm()->getData();

        $county = $this->countyRepository->findOneBy(['id' => $data['county']]);
        $this->addElements($form, $county);
    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function onPreSetData(FormEvent $event): void
    {
        /** @var Company $user */
        $user = $event->getData();
        $form = $event->getForm();

        $county = $user->getCity()?->getCounty();

        $this->addElements($form, $county);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gallery::class
        ]);
    }
}
