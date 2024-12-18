<?php

namespace App\Form\Type;

use App\Entity\City;
use App\Entity\County;
use App\Entity\EducationRegistration;
use App\Repository\CityRepository;
use App\Repository\CountyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class FormRegisterType extends AbstractType
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
        $user = $options['user'];

        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'data' => null === $user ? '' : $user->getFirstName(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s\-]+$/',
                        'message' => 'common.custom.first_name'
                    ])
                ]
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'data' => null === $user ? '' : $user->getLastName(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s\-]+$/',
                        'message' => 'common.custom.last_name'
                    ])
                ]
            ])
            ->add('cnp', TextType::class, [
                'required' => true,
                'data' => null === $user ? '' : $user->getCnp(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Length([
                        'min' => 13,
                        'minMessage' => 'common.cnp_message'
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9]\d{12}$/',
                        'message' => 'common.custom.cnp'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'data' => null === $user ? '' : $user->getEmail(),
                'constraints' => [
                    new Email([
                        'message' => 'common.not_valid.email'
                    ]),
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('phone', TextType::class, [
                'required' => true,
                'data' => null === $user ? '' : $user->getPhoneNumber(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Regex([
                        'pattern' => '/^(\+4|)?(07[0-8]{1}[0-9]{1}|02[0-9]{2}|03[0-9]{2}){1}?(\s|\.|\-)?([0-9]{3}(\s|\.|\-|)){2}$/',
                        'message' => 'common.not_valid.phone'
                    ]),
                ]
            ])
            ->add('county', EntityType::class, [
                'class' => County::class,
                'required' => true,
                'placeholder' => 'common.form_labels.choose_county',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.id', 'ASC');
                },
                'choice_label' => 'name',
                'data' => $user->getCounty(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('invoicingPerLegalEntity', CheckboxType::class, [
                'required' => false
            ])
            ->add('companyName', TextType::class, [
                'required' => false,
                'data' => null === $user ? '' : $user->getCompanyName(),
                'attr' => [
                    'readonly' => true,
                ],
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('companyAddress', TextType::class, [
                'required' => false,
                'attr' => [
                    'readonly' => true,
                ],
                'data' => null === $user ? '' : $user->getCompanyAddress(),
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('cui', TextType::class, [
                'required' => false,
                'attr' => [
                    'readonly' => true,
                ],
                'data' => null === $user ? '' : $user->getCui(),
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('registrationNumber', TextType::class, [
                'required' => false,
                'attr' => [
                    'readonly' => true,
                ],
                'data' => null === $user ? '' : $user->getRegistrationNumber(),
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('bankName', TextType::class, [
                'required' => false,
                'attr' => [
                    'readonly' => true,
                ],
                'data' => null === $user ? '' : $user->getBankName(),
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('bankAccount', TextType::class, [
                'required' => false,
                'attr' => [
                    'readonly' => true,
                ],
                'data' => null === $user ? '' : $user->getBankAccount(),
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'required' => true,
                'choices' => EducationRegistration::getPaymentMethods(),
                'expanded' => true,
                'multiple' => false,
                'data' => 'card'
            ])

            ->add('accordGDPR', CheckboxType::class, [
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('accordMedia', CheckboxType::class, [
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('googlePayToken', HiddenType::class, [
                'mapped' => false
            ])
            ->add('applePayToken', HiddenType::class, [
                'mapped' => false
            ])
        ;

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        $county = $this->countyRepository->findOneBy(['id' => $data['county']]);
        $this->addElements($form, $county);
    }

    /**
     * @param FormInterface $form
     * @param County|null $county
     * @return void
     */
    protected function addElements(FormInterface $form, County $county = null, City $city = null): void
    {
        $cities = $this->cityRepository->findBy(['county' => $county], ['name' => 'ASC']);

        $form->add('city', EntityType::class, [
            'required' => true,
            'class' => City::class,
            'choices' => $cities,
            'placeholder' => 'common.form_labels.choose_city',
            'choice_label' => 'name',
            'data' => $city,
            'constraints' => [
                new NotBlank([
                    'message' => 'common.not_blank'
                ])
            ]
        ]);
    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $user = $form->getConfig()->getOption('user');
        $county = $user->getCounty();
        $city = $user->getCity();

        $this->addElements($event->getForm(), $county, $city);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data' => EducationRegistration::class,
            'user' => null
        ]);
    }
}
