<?php

namespace App\Form\Type;

use App\Entity\NursingHome;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\CookMenu;

class CookMenuFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $user */
        $user = $options['user'];

        $builder
            ->add('name', TextType::class, [
                'required' => true
            ])
            ->add('nursingHome', EntityType::class, [
                'class' => NursingHome::class,
                'placeholder' => 'Selectează un cămin',
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($user) {
                    $queryBuilder = $er->createQueryBuilder('nh');

                    if ($user->hasRole('ROLE_ADMIN')) {
                        $queryBuilder
                            ->where('nh.user = :user')
                            ->setParameter('user', $user);
                    } else {
                        $queryBuilder
                            ->where('nh.id = :nursingHome')
                            ->setParameter('nursingHome', $user->getNursingHome());
                    }

                    return $queryBuilder
                        ->orderBy('nh.id', 'ASC');
                },
                'choice_label' => 'name',
            ])
            ->add('startDate', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'html5' => false
            ])
            ->add('endDate', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'html5' => false
            ])
            ->add('observations', TextareaType::class, [
                'required' => false
            ])
            ->add('cookMenuItems', CollectionType::class, [
                'entry_type' => CookMenuItemFormType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CookMenu::class,
            'user' => User::class
        ]);
    }

}
