<?php

namespace App\Form\Type;

use App\Entity\City;
use App\Entity\County;
use App\Entity\User;
use App\Repository\CityRepository;
use App\Repository\CountyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractType
{

    protected CityRepository $cityRepository;
    protected CountyRepository $countyRepository;
    private TranslatorInterface $translator;

    public function __construct(CityRepository $cityRepository, CountyRepository $countyRepository, TranslatorInterface $translator)
    {
        $this->cityRepository = $cityRepository;
        $this->countyRepository = $countyRepository;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
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
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new Email([
                        'message' => 'common.not_valid.email'
                    ]),
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_valid.phone'
                    ]),
                    new Regex([
                        'pattern' => '/^(\+4|)?(07[0-8]{1}[0-9]{1}|02[0-9]{2}|03[0-9]{2}){1}?(\s|\.|\-)?([0-9]{3}(\s|\.|\-|)){2}$/',
                        'message' => 'common.not_valid.phone'
                    ]),
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'required' => true,
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'common.not_valid.password_not_match',
                'first_options'  => ['label' => 'common.form_labels.password.first_options'],
                'second_options' => ['label' => 'common.form_labels.password.second_options'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'common.min_message'
                    ])
                ],
            ])
            ->add('county', EntityType::class, [
                'class' => County::class,
                'required' => true,
                'placeholder' => 'common.form_labels.choose_county',
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
            ->add('accordGDPR', CheckboxType::class, [
                'attr' => ['class' => 'form-control form-control-solid form-control-lg'],
                'label' => 'form_register.invoice',
                'label_html' => true
            ])
            ->add('newsletter', CheckboxType::class, [
                'attr' => ['class' => 'form-control form-control-solid form-control-lg'],
                'label' => 'form_register.invoice',
                'required' => false,
                'label_html' => true
            ])
        ;

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
            'placeholder' => 'common.form_labels.choose_city',
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

        $county = $this->countyRepository->findOneBy(['id' => $data['county']]);
        $this->addElements($form, $county);

    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function onPreSetData(FormEvent $event): void
    {
        $user = $event->getData();
        $form = $event->getForm();

        $county = $user->getCity()?->getCounty();

        $this->addElements($form, $county);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
