<?php

namespace App\Controller\Dashboard;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Form\Type\ArticleType;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class ArticleController extends AbstractController
{
    #[Route('/dashboard/articles', name: 'dashboard_article_index')]
    public function index(): Response
    {
        return $this->render('dashboard/article/index.html.twig');
    }

    #[Route('/dashboard/article/create', name: 'dashboard_article_create')]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader): Response
    {
        $language = $languageHelper->getDefaultLanguage();

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUuid(Uuid::v4());
            $file = $form->get('image')->getData();

            $articleTranslation = new ArticleTranslation();
            $articleTranslation->setArticle($article);
            $articleTranslation->setLanguage($language);
            $articleTranslation->setTitle($form->get('title')->getData());
            $articleTranslation->setDescription($form->get('description')->getData());
            $articleTranslation->setShortDescription($form->get('shortDescription')->getData());

            $uploadFile = $fileUploader->uploadFile(
                $file,
                $form,
                $this->getParameter('app_article_path')
            );

            if ($uploadFile['success']) {
                $article->setImageName($uploadFile['fileName']);
            }

            $em->persist($article);
            $em->persist($articleTranslation);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully added a new article.');
            return $this->redirectToRoute('dashboard_article_index');
        }

        return $this->render('dashboard/article/management.html.twig', [
            'form' => $form->createView(),
            'editMode' => false,
        ]);
    }

    #[Route('/dashboard/article/{uuid}/edit', name: 'dashboard_article_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid): Response
    {
        $article = $em->getRepository(Article::class)->findOneBy(['uuid' => $uuid]);
        if (null === $article) {
            return $this->redirectToRoute('dashboard_index');
        }

        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $language = $languageHelper->getLanguageByLocale($locale);

        $articleTranslation = $em->getRepository(ArticleTranslation::class)->findOneBy([
            'article' => $article,
            'language' => $language
        ]);

        if (null === $articleTranslation) {
            $articleTranslation = new ArticleTranslation();
            $articleTranslation->setArticle($article);
            $articleTranslation->setLanguage($language);
        }

        $form = $this->createForm(ArticleType::class, $article, [
            'translation' => $articleTranslation
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();

            $articleTranslation->setTitle($form->get('title')->getData());
            $articleTranslation->setDescription($form->get('description')->getData());
            $articleTranslation->setShortDescription($form->get('shortDescription')->getData());

            $uploadFile = $fileUploader->uploadFile(
                $file,
                $form,
                $this->getParameter('app_article_path')
            );

            if ($uploadFile['success']) {
                $article->setImageName($uploadFile['fileName']);
            }

            $em->persist($article);
            $em->persist($articleTranslation);
            $em->flush();

            $this->addFlash('success', 'You have successfully edited the article');
            return $this->redirectToRoute('dashboard_article_index');
        }

        return $this->render('dashboard/article/management.html.twig', [
            'form' => $form->createView(),
            'image' => $article->getImageName(),
            'entity' => $article,
            'editMode' => true
        ]);
    }

    #[Route('/dashboard/article/{uuid}/delete', name: 'dashboard_article_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        $article = $em->getRepository(Article::class)->findOneBy(['uuid' => $uuid]);
        if (null === $article) return $this->redirectToRoute('dashboard_index');

        $article->setDeletedAt(new \DateTime());
        $em->persist($article);
        $em->flush();

        $this->addFlash('success', 'The article has been successfully deleted');
        return $this->redirectToRoute('dashboard_article_index');
    }
}
