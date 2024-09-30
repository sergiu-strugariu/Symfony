<?php

namespace App\Form\Type;

use App\Entity\CategoryJob;
use App\Entity\City;
use App\Entity\Company;
use App\Entity\County;
use App\Entity\Job;
use App\Entity\JobTranslation;
use App\Entity\Language;
use App\Entity\User;
use App\Repository\CityRepository;
use App\Repository\CountyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class JobFormType extends AbstractType
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
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @param CityRepository $cityRepository
     * @param CountyRepository $countyRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(CityRepository $cityRepository, CountyRepository $countyRepository, TranslatorInterface $translator)
    {
        $this->cityRepository = $cityRepository;
        $this->countyRepository = $countyRepository;
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var JobTranslation $translation */
        $translation = $options['translation'];

        /** @var Language $language */
        $language = $options['language'];

        /** @var User $user */
        $user = $options['user'];

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getTitle() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
                    ])
                ]
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'required' => true,
                'placeholder' => 'common.select',
                'query_builder' => function (EntityRepository $er) use ($user) {
                    $queryBuilder = $er->createQueryBuilder('c')
                        ->where('c.deletedAt IS NULL')
                        ->andWhere('c.status = :status')
                        ->setParameter('status', Company::STATUS_PUBLISHED);

                    if (isset($user)) {
                        $queryBuilder
                            ->andWhere('c.user = :user')
                            ->setParameter('user', $user);
                    }

                    $queryBuilder
                        ->orderBy('c.id', 'ASC');

                    return $queryBuilder;
                },
                'choice_label' => 'name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
                    ])
                ]
            ])
            ->add('benefits', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? json_encode($translation->getBenefits()) : [],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
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
                        'message' => 'dashboard.form.field_mandatory',
                    ])
                ]
            ])
            ->add('categoryJobs', EntityType::class, [
                'class' => CategoryJob::class,
                'placeholder' => 'common.select',
                'required' => true,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.status = :published')
                        ->andWhere('c.deletedAt IS NULL')
                        ->setParameter('published', CategoryJob::STATUS_PUBLISHED)
                        ->orderBy('c.id', 'ASC');
                },
                'choice_label' => function (CategoryJob $categoryJob) use ($language) {
                    $collection = $categoryJob->getCategoryJobTranslations()->filter(function ($translation) use ($language) {
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
                    ]),
                    new Assert\Count([
                        'min' => 1,
                        'max' => 1,
                        'exactMessage' => 'dashboard.form.min_collection',
                    ])
                ]
            ])
            ->add('startGrossSalary', NumberType::class, [
                'required' => true,
                'scale' => 0,
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
            ->add('endGrossSalary', NumberType::class, [
                'required' => true,
                'scale' => 0,
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
            ->add('jobType', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
                    ])
                ],
                'choices' => Job::getJobTypes()
            ])
            ->add('address', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
                    ])
                ]
            ])
            ->add('status', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
                    ])
                ],
                'choices' => Job::getStatuses()
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
            ->add('body', TextareaType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getBody() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
                    ])
                ]
            ]);

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'])
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
    public function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $form->getData();

        $startGrossSalary = $data->getStartGrossSalary();
        $endGrossSalary = $data->getEndGrossSalary();

        if ($startGrossSalary !== null && $endGrossSalary !== null && $startGrossSalary >= $endGrossSalary) {
            $form->get('startGrossSalary')->addError(new FormError($this->translator->trans('dashboard.form.min_start_salary', [], 'messages')));
        }
    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();
        $entity = $form->getData();

        if ($entity->getSlug() == null && isset($data['title'])) {
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($data['title'])->lower();

            $entity->setSlug($slug);
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
            'data_class' => Job::class,
            'translation' => null,
            'language' => null,
            'user' => null
        ]);
    }
}
