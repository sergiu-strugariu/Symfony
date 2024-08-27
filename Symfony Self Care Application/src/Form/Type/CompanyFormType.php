<?php

namespace App\Form\Type;

use App\Entity\CategoryCare;
use App\Entity\CategoryService;
use App\Entity\City;
use App\Entity\County;
use App\Entity\Language;
use App\Entity\Company;
use App\Repository\CityRepository;
use App\Repository\CountyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Regex;

class CompanyFormType extends AbstractType
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
        /** @var Language $language */
        $language = $options['language'];

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.name.required'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'form.name.minlength'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s-]+$/',
                        'message' => 'form.name.valid_name'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.email.required'
                    ]),
                    new Assert\Email([
                        'message' => 'form.email.email'
                    ])
                ]
            ])
            ->add('phone', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.phone.required'
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'form.phone.minlength'
                    ]),
                    new Assert\Length([
                        'max' => 10,
                        'minMessage' => 'form.phone.maxlength'
                    ]),
                    new Regex([
                        'pattern' => '/^(?:(?:(?:\+4)?07\d{2}\d{6}|(?:\+4)?(21|31)\d{1}\d{6}|(?:\+4)?((2|3)[3-7]\d{1})\d{6}|(?:\+4)?(8|9)0\d{1}\d{6}))$/',
                        'message' => 'form.phone.phone_ro'
                    ]),
                ]
            ])
            ->add('website', UrlType::class, [
                'required' => true,
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
            ->add('address', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('postalCode', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('companyType', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ],
                'choices' => [
                    'common.public' => 'common.public',
                    'common.private' => 'common.private',
                ]
            ])
            ->add('companyCapacity', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ]),
                    new Assert\Range([
                        'min' => 1,
                        'minMessage' => 'form.default.min_message'
                    ]),
                ],
            ])
            ->add('admissionCriteria', ChoiceType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ],
                'choices' => Company::getAdmissionCriteriaRange()
            ])
            ->add('availableServices', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('price', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\d+(\.\d{1,2})?$/',
                        'message' => 'form.price.regex',
                    ]),
                    new Assert\Positive([
                        'message' => 'form.price.positive'
                    ])
                ]
            ])
            ->add('shortDescription', TextareaType::class, [
                'required' => true,
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
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('videoUrl', UrlType::class, [
                'required' => false
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
            ->add('fileName', FileType::class, [
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
            ->add('logo', FileType::class, [
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
            ->add('status', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ],
                'choices' => Company::getStatuses()
            ])
            ->add('categoryCares', EntityType::class, [
                'class' => CategoryCare::class,
                'required' => true,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.status = :published')
                        ->andWhere('c.deletedAt IS NULL')
                        ->setParameter('published', CategoryCare::STATUS_PUBLISHED)
                        ->orderBy('c.id', 'ASC');
                },
                'choice_label' => function (CategoryCare $category) use ($language) {
                    $collection = $category->getCategoryCareTranslations()->filter(function ($translation) use ($language) {
                        return $translation->getLanguage() === $language;
                    });

                    if (!$collection->isEmpty()) {
                        return $collection->first()->getTitle();
                    }

                    return null;
                },
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
                        'groups' => [Company::LOCATION_TYPE_CARE]
                    ]),
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'dashboard.form.min_select',
                        'groups' => [Company::LOCATION_TYPE_CARE]
                    ])
                ]
            ])
            ->add('categoryServices', EntityType::class, [
                'class' => CategoryService::class,
                'required' => false,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.status = :published')
                        ->andWhere('c.deletedAt IS NULL')
                        ->setParameter('published', CategoryService::STATUS_PUBLISHED)
                        ->orderBy('c.id', 'ASC');
                },
                'choice_label' => function (CategoryService $category) use ($language) {
                    $collection = $category->getCategoryServiceTranslations()->filter(function ($translation) use ($language) {
                        return $translation->getLanguage() === $language;
                    });

                    if (!$collection->isEmpty()) {
                        return $collection->first()->getTitle();
                    }

                    return null;
                },
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
                        'groups' => [Company::LOCATION_TYPE_PROVIDER]
                    ]),
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'dashboard.form.min_select',
                        'groups' => [Company::LOCATION_TYPE_PROVIDER]
                    ])
                ]
            ]);

        $builder
            ->get('availableServices')
            ->addModelTransformer(new CallbackTransformer(
            // Transform the array into a JSON string to be used by Tagify in the view
                function ($tagsArray) {
                    if ($tagsArray === null) {
                        return '[]';
                    }
                    return json_encode(array_map(function ($tag) {
                        return ['value' => $tag];
                    }, $tagsArray));
                },
                // Transform the JSON string into an array to be saved in the entity
                function ($tagsJson) {
                    $tagsArray = json_decode($tagsJson, true);
                    if ($tagsArray === null) {
                        return [];
                    }
                    return array_map(function ($tag) {
                        return $tag['value'];
                    }, $tagsArray);
                }
            ));

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

        /** @var Company $company */
        $company = $event->getForm()->getData();

        if ($company->getSlug() == null && isset($data['name'])) {
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($data['name'])->lower();

            $company->setSlug($slug);
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
            'language' => null,
            'locationType' => Company::LOCATION_TYPE_CARE,
            'validation_groups' => function ($form) {
                $options = $form->getConfig()->getOptions();
                $locationType = $options['locationType'];
                return $locationType === Company::LOCATION_TYPE_CARE
                    ? ['Default', Company::LOCATION_TYPE_CARE]
                    : ['Default', Company::LOCATION_TYPE_PROVIDER];
            }
        ]);
    }
}
