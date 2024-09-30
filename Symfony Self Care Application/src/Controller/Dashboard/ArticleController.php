<?php

namespace App\Controller\Dashboard;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Form\Type\ArticleFormType;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleController extends AbstractController
{
    #[Route('/dashboard/articles', name: 'dashboard_article_index')]
    public function index(): Response
    {
        return $this->render('dashboard/article/index.html.twig');
    }

    #[Route('/dashboard/secure/generate-article', name: 'dashboard_generate_article_index')]
    public function generateArticle(): Response
    {
        return $this->render('dashboard/article/generate-article.html.twig');
    }

    #[Route('/dashboard/article/create', name: 'dashboard_article_create')]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, TranslatorInterface $translator): Response
    {
        // get default language
        $language = $languageHelper->getDefaultLanguage();

        $article = new Article();
        $form = $this->createForm(ArticleFormType::class, $article, ['language' => $language]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // generate and set unique UUID
            $article->setUuid(Uuid::v4());
            $article->setUser($this->getUser());

            // get data from the form
            $file = $form->get('fileName')->getData();

            // create translation and set data
            $articleTranslation = new ArticleTranslation();
            $articleTranslation->setArticle($article);
            $articleTranslation->setLanguage($language);
            $articleTranslation->setTitle($form->get('title')->getData());
            $articleTranslation->setBody($form->get('body')->getData());
            $articleTranslation->setShortDescription($form->get('shortDescription')->getData());

            $article->addArticleTranslation($articleTranslation);

            if (isset($file)) {
                // Upload article file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_article_path')
                );

                // Check and set @filename
                if ($uploadFile['success']) {
                    // Set fileName file
                    $article->setFileName($uploadFile['fileName']);
                }
            }

            // save new item to DB
            $em->persist($article);
            $em->persist($articleTranslation);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));

            return $this->redirectToRoute('dashboard_article_index');
        }

        return $this->render('dashboard/article/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.create_article', [], 'messages')
        ]);
    }

    #[Route('/dashboard/article/{uuid}/edit', name: 'dashboard_article_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid, TranslatorInterface $translator): Response
    {
        $article = $em->getRepository(Article::class)->findOneBy(['uuid' => $uuid]);

        if (null === $article) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_article_index');
        }

        // get selected language
        $locale = $request->get('locale');
        $language = $languageHelper->getLanguageByLocale($locale);

        // get translation
        $articleTranslation = $em->getRepository(ArticleTranslation::class)->findOneBy([
            'article' => $article,
            'language' => $language
        ]);

        $articleTranslation = $articleTranslation ?? new ArticleTranslation();

        // init form & handle request data
        $form = $this->createForm(ArticleFormType::class, $article, [
            'translation' => $articleTranslation,
            'language' => $language
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get data from the form
            $file = $form->get('fileName')->getData();
            $fileUploaded = true;

            $article->setUpdatedAt(new DateTime());
            $articleTranslation->setTitle($form->get('title')->getData());
            $articleTranslation->setBody($form->get('body')->getData());
            $articleTranslation->setShortDescription($form->get('shortDescription')->getData());

            $articleTranslation->setArticle($article);
            $articleTranslation->setLanguage($language);

            // Check uploaded file
            if (isset($file)) {
                // Upload company file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_article_path')
                );

                // Set status uploaded
                $fileUploaded = $uploadFile['success'];

                // Check and set @filename
                if ($uploadFile['success']) {
                    $article->setFileName($uploadFile['fileName']);
                }
            }

            if ($fileUploaded) {
                // save changes to DB
                $em->persist($article);
                $em->persist($articleTranslation);
                $em->flush();

                // Set flash message
                $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages'));

                return $this->redirectToRoute('dashboard_article_index');
            }
        }

        return $this->render('dashboard/article/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.edit_article', [], 'messages'),
            'image' => $article->getFileName()
        ]);
    }

    #[Route('/dashboard/article/actions/{action}/{uuid}', name: 'dashboard_article_actions')]
    public function actions(EntityManagerInterface $em, $action, $uuid, TranslatorInterface $translator): Response
    {
        /** @var Article $article */
        $article = $em->getRepository(Article::class)->findOneBy(['uuid' => $uuid]);

        if (!isset($article)) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_article_index');
        }

        if ($action === 'remove') {
            // Soft delete
            $article->setDeletedAt(new DateTime());
        } elseif ($action === 'moderate') {
            $article->setStatus($article->getStatus() === Article::STATUS_DRAFT ? Article::STATUS_PUBLISHED : Article::STATUS_DRAFT);
        } else {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
            return $this->redirectToRoute('dashboard_article_index');
        }

        // Update data
        $em->persist($article);
        $em->flush();

        // Set flash message
        $this->addFlash('success', sprintf($translator->trans('controller.success_multiple', [], 'messages'), $action === 'moderate' ? $translator->trans('controller.moderated', [], 'messages') : $translator->trans('controller.deleted', [], 'messages')));

        // Redirect to listing page
        return $this->redirectToRoute('dashboard_article_index');
    }
}
