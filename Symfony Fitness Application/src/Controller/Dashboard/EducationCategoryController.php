<?php

namespace App\Controller\Dashboard;

use App\Entity\Article;
use App\Entity\EducationCategory;
use App\Entity\EducationCategoryTranslation;
use App\Form\Type\EducationCategoryType;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class EducationCategoryController extends AbstractController
{
    #[Route('/dashboard/categories', name: 'dashboard_categories_index')]
    public function index(): Response
    {
        return $this->render('dashboard/categories/index.html.twig');
    }

    #[Route('/dashboard/category/create', name: 'dashboard_category_create')]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader): Response
    {
        $language = $languageHelper->getDefaultLanguage();

        $category = new EducationCategory();
        $form = $this->createForm(EducationCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setUuid(Uuid::v4());
            $file = $form->get('fileName')->getData();

            $categoryTranslation = new EducationCategoryTranslation();
            $categoryTranslation->setEducationCategory($category);
            $categoryTranslation->setLanguage($language);
            $categoryTranslation->setTitle($form->get('title')->getData());
            $categoryTranslation->setDescription($form->get('description')->getData());

            $uploadFile = $fileUploader->uploadFile(
                $file,
                $form,
                $this->getParameter('app_categories_path')
            );

            if ($uploadFile['success']) {
                $category->setFileName($uploadFile['fileName']);
            }

            $em->persist($category);
            $em->persist($categoryTranslation);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully added a new category.');
            return $this->redirectToRoute('dashboard_categories_index');
        }

        return $this->render('dashboard/categories/management.html.twig', [
            'form' => $form->createView(),
            'editMode' => false,
        ]);
    }

    #[Route('/dashboard/category/{uuid}/edit', name: 'dashboard_category_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid): Response
    {
        $category = $em->getRepository(EducationCategory::class)->findOneBy(['uuid' => $uuid]);
        if (null === $category) {
            return $this->redirectToRoute('dashboard_categories_index');
        }

        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $language = $languageHelper->getLanguageByLocale($locale);

        $categoryTranslation = $em->getRepository(EducationCategoryTranslation::class)->findOneBy([
            'educationCategory' => $category,
            'language' => $language
        ]);

        if (null === $categoryTranslation) {
            $categoryTranslation = new EducationCategoryTranslation();
            $categoryTranslation->setEducationCategory($category);
            $categoryTranslation->setLanguage($language);
        }

        $form = $this->createForm(EducationCategoryType::class, $category, [
            'translation' => $categoryTranslation
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('fileName')->getData();

            $categoryTranslation->setTitle($form->get('title')->getData());
            $categoryTranslation->setDescription($form->get('description')->getData());

            if ($file) {
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_categories_path')
                );

                if ($uploadFile['success']) {
                    $category->setFileName($uploadFile['fileName']);
                }
            }

            $em->persist($category);
            $em->persist($categoryTranslation);
            $em->flush();

            $this->addFlash('success', 'You have successfully edited the category');
            return $this->redirectToRoute('dashboard_categories_index');
        }

        return $this->render('dashboard/categories/management.html.twig', [
            'form' => $form->createView(),
            'image' => $category->getFileName(),
            'entity' => $category,
            'editMode' => true
        ]);
    }

    #[Route('/dashboard/category/{uuid}/delete', name: 'dashboard_category_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        $category = $em->getRepository(EducationCategory::class)->findOneBy(['uuid' => $uuid]);
        if (null === $category) return $this->redirectToRoute('dashboard_index');

        $category->setDeletedAt(new \DateTime());
        $em->persist($category);
        $em->flush();

        $this->addFlash('success', 'The category has been successfully deleted');
        return $this->redirectToRoute('dashboard_categories_index');
    }
}
