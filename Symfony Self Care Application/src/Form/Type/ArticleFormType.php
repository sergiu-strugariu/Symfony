<?php

namespace App\Form\Type;

use App\Entity\Article;
use App\Entity\CategoryArticle;
use App\Entity\Job;
use App\Entity\JobTranslation;
use App\Entity\Language;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ArticleFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder,  array $options): void
    {
        /** @var Job $data */
        $data = $options['data'];

        /** @var JobTranslation $translation */
        $translation = $options['translation'];

        /** @var Language $language */
        $language = $options['language'];

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getTitle() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('categoryArticles', EntityType::class, [
                'class' => CategoryArticle::class,
                'placeholder' =>  'common.select',
                'required' => true,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) use ($language) {
                    return $er->createQueryBuilder('c')
                        ->where('c.status = :published')
                        ->andWhere('c.deletedAt IS NULL')
                        ->setParameter('published', CategoryArticle::STATUS_PUBLISHED)
                        ->orderBy('c.id', 'ASC');
                },
                'choice_label' => function (CategoryArticle $categoryArticle) use ($language) {
                    $collection = $categoryArticle->getCategoryArticleTranslations()->filter(function ($translation) use ($language) {
                        return $translation->getLanguage() === $language;
                    });

                    if (!$collection->isEmpty()) {
                        return $collection->first()->getTitle();
                    }

                    return null;
                },
                'constraints' => [
                    new Assert\NotBlank([
                        'message' =>  'dashboard.form.field_mandatory'
                    ]),
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' =>  'dashboard.form.min_select'
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
                'choices' => Article::getStatuses()
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
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $entity = $event->getForm()->getData();

        if ($entity->getSlug() == null && isset($data['title'])) {
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($data['title'])->lower();

            $entity->setSlug($slug);
        }
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'translation' => null,
            'language' => null
        ]);
    }
}
