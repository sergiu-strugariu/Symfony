<?php

namespace App\Form\Type;

use App\Entity\Certification;
use App\Entity\City;
use App\Entity\County;
use App\Entity\Education;
use App\Entity\TeamMember;
use App\Repository\CityRepository;
use App\Repository\CountyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

class EducationType extends AbstractType
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
        $translation = $options['translation'];
        $locale = $options['locale'];

        $imageConstraints = [
            new Assert\File([
                'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
                'mimeTypesMessage' => 'common.not_blank',
                'maxSize' => '3M'
            ])
        ];
        $slugConstraints = [];

        if (null === $data->getId()) {
            $additionalImageConstraints = [
                new Assert\NotBlank([
                    'message' => 'common.not_blank'
                ])
            ];
            $imageConstraints = array_merge($imageConstraints, $additionalImageConstraints);
        } else {
            $slugConstraints = [
                new Assert\NotBlank([
                    'message' => 'common.not_blank'
                ])
            ];
        }

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getTitle() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('certification', EntityType::class, [
                'class' => Certification::class,
                'choice_label' => function (Certification $certification) use ($locale) {
                    return $certification->getTranslation($locale)->getTitle();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.deletedAt IS NULL');
                },
                'required' => true,
            ])
            ->add('slug', TextType::class, [
                'required' => null === $data->getId() ? false : true,
                'disabled' => null === $data->getId() ? true : false,
                'constraints' => $slugConstraints
            ])
            ->add('location', TextType::class, [
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
            ->add('shortDescription', TextareaType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getShortDescription() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getDescription() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('additionalInfo', TextareaType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getAdditionalInfo() : ''
            ])
            ->add('importantInfo', TextareaType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getImportantInfo() : ''
            ])
            ->add('price', NumberType::class, [
                'required' => true,
                'html5' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('vat', NumberType::class, [
                'required' => true,
                'html5' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('finalPrice', NumberType::class, [
                'required' => false,
                'disabled' => true,
                'mapped' => false,
                'html5' => true
            ])
            ->add('discount', NumberType::class, [
                'required' => false,
                'html5' => true
            ])
            ->add('discountStartDate', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => false
            ])
            ->add('discountEndDate', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => false
            ])
            ->add('startDate', DateTimeType::class, [
                'required' => true,
                'widget' => 'single_text',
                'html5' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])

            ->add('endDate', DateTimeType::class, [
                'required' => true,
                'widget' => 'single_text',
                'html5' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('teamMembers', EntityType::class, [
                'class' => TeamMember::class,
                'required' => false,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('tm')
                        ->where('tm.deletedAt IS NULL')
                        ->orderBy('tm.name', 'ASC');
                },
                'choice_label' => 'name'
            ])
            ->add('omcCode', TextType::class, [
                'required' => false
            ])
            ->add('allowRegistrations', CheckboxType::class, [
                'required' => false
            ])
            ->add('image', FileType::class, [
                'required' => !$data->getId(),
                'mapped' => false,
                'constraints' => $imageConstraints
            ])
            ->add('contractOccupation', TextType::class, [
                'required' => false
            ])
            ->add('contractDuration', TextareaType::class, [
                'required' => false
            ])
            ->add('invoiceServiceName', TextareaType::class, [
                'required' => false
            ]);

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'])
            ->addEventListener(FormEvents::POST_SUBMIT, [self::class, 'onPostSubmit']);
    }

    public static function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $form->getData();

        if ($data instanceof \App\Entity\Education) {
            $startDate = $data->getStartDate();
            $endDate = $data->getEndDate();

            if ($startDate && $endDate && $startDate >= $endDate) {
                $form->addError(new FormError('The end date must be later than the start date.'));
            }
        }
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

        if (null === $entity->getSlug() && isset($data['title'])) {
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($data['title'])->lower();

            $entity->setSlug($slug);
        }

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

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Education::class,
            'translation' => null,
            'locale' => null
        ]);
    }
}
