<?php

namespace App\Form\Type;

use App\Entity\City;
use App\Entity\Company;
use App\Entity\County;
use App\Entity\Event;
use App\Entity\EventPartner;
use App\Entity\EventSpeaker;
use App\Entity\EventTranslation;
use App\Entity\EventWinner;
use App\Helper\DefaultHelper;
use App\Repository\CityRepository;
use App\Repository\CountyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EventForm extends AbstractType
{
    /**
     * @var CityRepository
     */
    protected CityRepository $cityRepository;

    /**
     * @var CountyRepository
     */
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

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Event $event */
        $event = $options['data'];

        /** @var EventTranslation $translation */
        $translation = $options['translation'];

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getTitle() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.name.required'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'form.name.minlength'
                    ])
                ]
            ])
            ->add('shortDescription', TextareaType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getShortDescription() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.message.required'
                    ]),
                    new Assert\Length([
                        'min' => 20,
                        'minMessage' => 'form.message.minMessage'
                    ]),
                    new Assert\Length([
                        'max' => 110,
                        'minMessage' => 'form.message.maxMessage'
                    ]),
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getDescription() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('address', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('startDate', TextType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('county', EntityType::class, [
                'class' => County::class,
                'required' => true,
                'placeholder' => 'common.select',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.id', 'ASC');
                },
                'choice_label' => 'name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('fileName', FileType::class, [
                'required' => empty($event->getFileName()),
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'form.fileCv.format',
                        'maxSize' => '3M'
                    ]),
                    new Assert\Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($event) {
                            if (empty($event->getFileName()) && empty($value)) {
                                $context->buildViolation('dashboard.form.field_mandatory')->addViolation();
                            }
                        }
                    ])
                ]
            ])
            ->add('programFileName', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'form.fileCv.format',
                        'maxSize' => '3M'
                    ])
                ]
            ])
            ->add('videoPlaceholder', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'form.fileCv.format',
                        'maxSize' => '3M'
                    ])
                ]
            ])
            ->add('videoUrl', UrlType::class, [
                'required' => false
            ])
            ->add('status', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ],
                'choices' => DefaultHelper::getStatus()
            ])
            ->add('eventStatus', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ],
                'choices' => Event::getOptionStatus()
            ])
            ->add('eventPartnerSponsors', EntityType::class, [
                'class' => EventPartner::class,
                'required' => true,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ep')
                        ->where('ep.type = :type')
                        ->andWhere('ep.deletedAt IS NULL')
                        ->setParameter('type', EventPartner::SPONSOR_TYPE)
                        ->orderBy('ep.id', 'DESC');
                },
                'choice_label' => 'name',
                'choice_attr' => function (EventPartner $eventPartner) {
                    return ['data-filename' => $eventPartner->getFileName()];
                },
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ]),
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'dashboard.form.min_collection'
                    ])
                ]
            ])
            ->add('eventPartnerMedia', EntityType::class, [
                'class' => EventPartner::class,
                'required' => true,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ep')
                        ->where('ep.type = :type')
                        ->andWhere('ep.deletedAt IS NULL')
                        ->setParameter('type', EventPartner::MEDIA_TYPE)
                        ->orderBy('ep.id', 'DESC');
                },
                'choice_label' => 'name',
                'choice_attr' => function (EventPartner $eventPartner) {
                    return ['data-filename' => $eventPartner->getFileName()];
                },
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ]),
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'dashboard.form.min_collection'
                    ])
                ]
            ])
            ->add('eventSpeakers', EntityType::class, [
                'class' => EventSpeaker::class,
                'required' => true,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('es')
                        ->where('es.status = :status')
                        ->andWhere('es.deletedAt IS NULL')
                        ->setParameter('status', DefaultHelper::STATUS_PUBLISHED)
                        ->orderBy('es.id', 'DESC');
                },
                'choice_label' => 'fullName',
                'choice_attr' => function (EventSpeaker $eventSpeaker) {
                    return ['data-filename' => $eventSpeaker->getFileName(), 'data-type' => 'speakers'];
                },
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ]),
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'dashboard.form.min_collection'
                    ])
                ]
            ])
            ->add('eventWinners', EntityType::class, [
                'class' => Company::class,
                'required' => false,
                'multiple' => true,
                'mapped' => false,
                'data' => $options['eventWinners'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin('c.eventWinners', 'w')
                        ->where('c.status = :status')
                        ->andWhere('c.locationType = :type')
                        ->andWhere('c.deletedAt IS NULL')
                        ->setParameter('status', DefaultHelper::STATUS_PUBLISHED)
                        ->setParameter('type', Company::LOCATION_TYPE_CARE)
                        ->orderBy('w.position', 'ASC');
                },
                'choice_label' => 'name'
            ])
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
            'placeholder' => 'common.select',
            'choice_label' => 'name',
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'dashboard.form.field_mandatory',
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
        $form = $event->getForm();

        // Form data
        $data = $event->getData();

        /** @var Event $eventEnt */
        $eventEnt = $event->getForm()->getData();

        if ($eventEnt->getSlug() == null && isset($data['title'])) {
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($data['title'])->lower();

            $eventEnt->setSlug($slug);
        }

        if (!empty($data['videoUrl'])) {
            $form->add('videoPlaceholder', FileType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'form.fileCv.format',
                        'maxSize' => '3M'
                    ]),
                    new Assert\Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($eventEnt) {
                            if (empty($eventEnt->getVideoPlaceholder()) && empty($value)) {
                                $context->buildViolation('dashboard.form.field_mandatory')->addViolation();
                            }
                        }
                    ])
                ]
            ]);
        }

        if ($data['eventStatus'] === Event::STATUS_ENDED) {
            $form->add('eventWinners', EntityType::class, [
                'class' => Company::class,
                'required' => true,
                'multiple' => true,
                'mapped' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.status = :status')
                        ->andWhere('c.locationType = :type')
                        ->andWhere('c.deletedAt IS NULL')
                        ->setParameter('status', DefaultHelper::STATUS_PUBLISHED)
                        ->setParameter('type', Company::LOCATION_TYPE_CARE)
                        ->orderBy('c.id', 'DESC');
                },
                'choice_label' => 'name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ]),
                    new Assert\Count([
                        'min' => 3,
                        'minMessage' => 'dashboard.form.min_collection'
                    ]),
                    new Assert\Count([
                        'max' => 10,
                        'minMessage' => 'dashboard.form.min_collection'
                    ])
                ]
            ]);
        }

        /** @var County $county */
        $county = $this->countyRepository->findOneBy(['id' => $data['county']]);

        $this->addElements($form, $county);
    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function onPreSetData(FormEvent $event): void
    {
        /** @var Company $company */
        $company = $event->getData();
        $form = $event->getForm();

        $county = $company->getCity()?->getCounty();

        $this->addElements($form, $county);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'translation' => null,
            'eventWinners' => [],
        ]);
    }
}
