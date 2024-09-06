<?php

namespace App\Controller\Dashboard;

use App\Entity\CategoryCare;
use App\Entity\CategoryCareTranslation;
use App\Entity\CategoryService;
use App\Entity\CategoryServiceTranslation;
use App\Entity\Company;
use App\Entity\Language;
use App\Entity\CategoryArticle;
use App\Entity\CategoryArticleTranslation;
use App\Entity\CategoryCourseTranslation;
use App\Entity\CategoryJobTranslation;
use App\Entity\CategoryCourse;
use App\Entity\CategoryJob;
use App\Form\Type\DefaultCategoryFormType;
use App\Helper\LanguageHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use App\Helper\DefaultHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultCategoryController extends AbstractController
{
    #[Route('/dashboard/category/{type}', name: 'dashboard_default_category_index')]
    public function index($type, TranslatorInterface $translator): Response
    {
        // Check exist type
        if (empty($type) || !in_array($type, DefaultHelper::CATEGORY_TYPES)) {
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_index');
        }

        return $this->render('dashboard/default-category/index.html.twig', [
            'pageTitle' => ucfirst($type) . ' ' . $translator->trans('controller.categories', [], 'messages'),
            'type' => $type
        ]);
    }

    #[Route('/dashboard/category/create/{type}', name: 'dashboard_default_category_create')]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, $type, TranslatorInterface $translator): Response
    {
        $category = null;
        $categoryClass = null;
        $categoryTranslation = null;

        // Check exist type
        if (empty($type) || !in_array($type, DefaultHelper::CATEGORY_TYPES)) {
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_default_category_index', ['type' => $type]);
        }

        // Check type and set entity
        switch ($type) {
            case 'training':
                $category = new CategoryCourse();
                $categoryClass = CategoryCourse::class;
                break;
            case 'article':
                $category = new CategoryArticle();
                $categoryClass = CategoryArticle::class;
                break;
            case 'job':
                $category = new CategoryJob();
                $categoryClass = CategoryJob::class;
                break;
            case Company::LOCATION_TYPE_CARE:
                $category = new CategoryCare();
                $categoryClass = CategoryCare::class;
                break;
            case Company::LOCATION_TYPE_PROVIDER:
                $category = new CategoryService();
                $categoryClass = CategoryService::class;
                break;
        }

        $form = $this->createForm(DefaultCategoryFormType::class, $category, ['data_class' => $categoryClass]);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * Get all available languages
             * @var Language $languages
             */
            $languages = $languageHelper->getAllLanguage();

            // generate and set unique UUID
            $category->setUuid(Uuid::v4());
            $title = $form->get('title')->getData();

            foreach ($languages as $language) {
                // Create a new translation object for each language
                switch ($type) {
                    case 'training':
                        $categoryTranslation = new CategoryCourseTranslation();
                        $categoryTranslation->setCategoryCourse($category);
                        break;
                    case 'article':
                        $categoryTranslation = new CategoryArticleTranslation();
                        $categoryTranslation->setCategoryArticle($category);
                        break;
                    case 'job':
                        $categoryTranslation = new CategoryJobTranslation();
                        $categoryTranslation->setCategoryJob($category);
                        break;
                    case Company::LOCATION_TYPE_CARE:
                        $categoryTranslation = new CategoryCareTranslation();
                        $categoryTranslation->setCategoryCare($category);
                        break;
                    case Company::LOCATION_TYPE_PROVIDER:
                        $categoryTranslation = new CategoryServiceTranslation();
                        $categoryTranslation->setCategoryService($category);
                        break;
                }

                $categoryTranslation->setLanguage($language);
                $categoryTranslation->setTitle($title);
                $em->persist($categoryTranslation);
            }

            // save changes to DB
            $em->persist($category);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages') . $type);
            return $this->redirectToRoute('dashboard_default_category_index', ['type' => $type]);
        }

        return $this->render('dashboard/default-category/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('dashboard.form.add_new_category', ["%type%" => $type], 'messages'),
            'type' => $type
        ]);
    }

    #[Route('/dashboard/category/{uuid}/edit/{type}', name: 'dashboard_default_category_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, $uuid, $type, TranslatorInterface $translator): Response
    {
        $categoryClass = null;
        $categoryClassTranslation = null;
        $categoryRelationTranslate = '';

        // Check exist type
        if (empty($type) || !in_array($type, DefaultHelper::CATEGORY_TYPES)) {
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_default_category_index', ['type' => $type]);
        }

        // Get selected language
        $language = $languageHelper->getLanguageByLocale($request->get('locale'));

        // Check type and set entity
        switch ($type) {
            case 'training':
                $categoryClass = CategoryCourse::class;
                $categoryClassTranslation = CategoryCourseTranslation::class;
                $categoryRelationTranslate = 'categoryCourse';
                $setCategoryRelation = 'setCategoryCourse';
                break;
            case 'article':
                $categoryClass = CategoryArticle::class;
                $categoryClassTranslation = CategoryArticleTranslation::class;
                $categoryRelationTranslate = 'categoryArticle';
                $setCategoryRelation = 'setCategoryArticle';
                break;
            case 'job':
                $categoryClass = CategoryJob::class;
                $categoryClassTranslation = CategoryJobTranslation::class;
                $categoryRelationTranslate = 'categoryJob';
                $setCategoryRelation = 'setCategoryJob';
                break;
            case Company::LOCATION_TYPE_CARE:
                $categoryClass = CategoryCare::class;
                $categoryClassTranslation = CategoryCareTranslation::class;
                $categoryRelationTranslate = 'categoryCare';
                $setCategoryRelation = 'setCategoryCare';
                break;
            case Company::LOCATION_TYPE_PROVIDER:
                $categoryClass = CategoryService::class;
                $categoryClassTranslation = CategoryServiceTranslation::class;
                $categoryRelationTranslate = 'categoryService';
                $setCategoryRelation = 'setCategoryService';
                break;
        }

        // Get category by @uuid
        $category = $em->getRepository($categoryClass)->findOneBy(['uuid' => $uuid]);

        if (null === $category) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));

            return $this->redirectToRoute('dashboard_default_category_index', ['type' => $type]);
        }

        // Get translation by @lang
        $categoryTranslation = $em->getRepository($categoryClassTranslation)->findOneBy([
            $categoryRelationTranslate => $category,
            'language' => $language
        ]);

        $categoryTranslation = $categoryTranslation ?? new $categoryClassTranslation;

        // Init form & handle request data
        $form = $this->createForm(DefaultCategoryFormType::class, $category, [
            'data_class' => $categoryClass,
            'translation' => $categoryTranslation
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Set data in ro
            $categoryTranslation->setLanguage($language);
            $categoryTranslation->setTitle($form->get('title')->getData());

            // Set the relation dynamically
            $categoryTranslation->$setCategoryRelation($category);

            // save changes to DB
            $em->persist($category);
            $em->persist($categoryTranslation);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages') . ' - '  . $categoryTranslation->getTitle());
            return $this->redirectToRoute('dashboard_default_category_index', ['type' => $type]);
        }

        return $this->render('dashboard/default-category/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('dashboard.actions.edit', [], 'messages') . ' ' . $type,
            'type' => $type,
        ]);
    }

    #[Route('/dashboard/category/actions/{action}/{uuid}/{type}', name: 'dashboard_default_category_actions')]
    public function actions(EntityManagerInterface $em, $action, $uuid, $type, TranslatorInterface $translator): Response
    {
        $categoryClass = null;
        $path = 'dashboard_index';

        // Check exist type
        if (empty($type) || !in_array($type, DefaultHelper::CATEGORY_TYPES)) {
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute($path);
        }

        // Check type and set entity
        switch ($type) {
            case 'training':
                $categoryClass = CategoryCourse::class;
                break;
            case 'article':
                $categoryClass = CategoryArticle::class;
                break;
            case 'job':
                $categoryClass = CategoryJob::class;
                break;
            case Company::LOCATION_TYPE_CARE:
                $categoryClass = CategoryCare::class;
                break;
            case Company::LOCATION_TYPE_PROVIDER:
                $categoryClass = CategoryService::class;
                break;
        }

        // Get category by @uuid
        $category = $em->getRepository($categoryClass)->findOneBy(['uuid' => $uuid]);

        if (null === $category) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));

            return $this->redirectToRoute('dashboard_default_category_index', ['type' => $type]);
        }

        if ($action === 'remove') {
            // Soft delete
            $category->setDeletedAt(new DateTime());
        } elseif ($action === 'moderate') {
            $category->setStatus($category->getStatus() === $category::STATUS_DRAFT ? $category::STATUS_PUBLISHED : $category::STATUS_DRAFT);
        } else {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
            return $this->redirectToRoute('dashboard_default_category_index', ['type' => $type]);
        }

        // Update data
        $em->persist($category);
        $em->flush();

        // Set flash message
        $this->addFlash('success', sprintf($translator->trans('controller.success_multiple', [], 'messages'), $action === 'moderate' ?  $translator->trans('controller.moderated', [], 'messages') : $translator->trans('controller.deleted', [], 'messages')));

        // Redirect to listing page
        return $this->redirectToRoute('dashboard_default_category_index', ['type' => $type]);
    }
}
