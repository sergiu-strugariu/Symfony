<?php

namespace App\Controller\Dashboard;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Entity\CategoryArticle;
use App\Entity\User;
use App\Form\Type\ArticleFormType;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use App\Helper\MembershipHelper;
use App\Helper\UserHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleController extends AbstractController
{
    #[Route('/dashboard/articles', name: 'dashboard_article_index')]
    public function index(): Response
    {
        return $this->render('dashboard/article/index.html.twig');
    }

    #[Route('/dashboard/generate-article', name: 'dashboard_generate_article_index')]
    public function generateArticle(EntityManagerInterface $em, TranslatorInterface $translator, MembershipHelper $helper): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $getPlan = $helper->checkMembership($user, Article::ENTITY_AI_NAME);

        if ($getPlan['status']) {
            $this->addFlash('danger', sprintf($translator->trans('dashboard.actions.max_plan', [], 'messages'), $getPlan['membership'], $getPlan['max']));
            return $this->redirectToRoute('dashboard_article_index');
        }

        /** @var CategoryArticle $categories */
        $categories = $em->getRepository(CategoryArticle::class)->findBy(['status' => DefaultHelper::STATUS_PUBLISHED], ['createdAt' => 'desc']);

        return $this->render('dashboard/article/generate-article.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/article/create', name: 'dashboard_article_create')]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, TranslatorInterface $translator, MembershipHelper $helper): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Get membership by @user
        $getPlan = $helper->checkMembership($user, Article::ENTITY_NAME);

        // Check status plan
        if ($getPlan['status']) {
            $this->addFlash('danger', sprintf($translator->trans('dashboard.actions.max_plan', [], 'messages'), $getPlan['membership'], $getPlan['max']));
            return $this->redirectToRoute('dashboard_article_index');
        }

        // get default language
        $language = $languageHelper->getDefaultLanguage();

        $article = new Article();
        $form = $this->createForm(ArticleFormType::class, $article, ['language' => $language]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUser($user);
            $article->setEndedAt(new DateTime($form->get('endedAt')->getData()));

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

            // Insert item in EntityLog
            $helper->insertEntityLog($article, Article::ENTITY_NAME);

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));

            return $this->redirectToRoute('dashboard_article_index');
        }

        return $this->render('dashboard/article/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.create_article', [], 'messages')
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/article/{uuid}/edit', name: 'dashboard_article_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, TranslatorInterface $translator, UserHelper $userHelper, $uuid): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Article $article */
        $article = $em->getRepository(Article::class)->findOneBy(['uuid' => $uuid]);

        if (empty($article)) {
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
            $article->setEndedAt(new DateTime($form->get('endedAt')->getData()));
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
                    // Remove old file
                    $userHelper->removeEntityFiles($article, Article::ENTITY_NAME);

                    // Set new fileName
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
            'image' => $article->getFileName(),
            'endedAt' => $article->getEndedAt()->format('d.m.Y')
        ]);
    }

    #[Route('/dashboard/article/actions/{action}/{uuid}', name: 'dashboard_article_actions')]
    public function actions(EntityManagerInterface $em, $action, TranslatorInterface $translator, MembershipHelper $helper, UserHelper $userHelper, $uuid): Response
    {
        /** @var Article $article */
        $article = $em->getRepository(Article::class)->findOneBy(['uuid' => $uuid]);

        if ($article === null) {
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_article_index');
        }

        /** @var User $user */
        $user = $article->getUser();

        // Get membership by @user
        $getPlan = $helper->checkMembership($user, Article::ENTITY_NAME);

        // Check status
        if ($getPlan['status'] && $article->getStatus() !== DefaultHelper::STATUS_PUBLISHED && $action === DefaultHelper::ACTION_MODERATE) {
            $this->addFlash('danger', sprintf($translator->trans('dashboard.actions.max_plan', [], 'messages'), $getPlan['membership'], $getPlan['max']));
            return $this->redirectToRoute('dashboard_article_index');
        }

        switch ($action) {
            case DefaultHelper::ACTION_REMOVE:
                // Remove storage files
                $userHelper->removeEntityFiles($article, Article::ENTITY_NAME);

                // Remove item
                $em->remove($article);
                break;
            case DefaultHelper::ACTION_MODERATE:
                $article->setStatus($article->getStatus() === DefaultHelper::STATUS_DRAFT ? DefaultHelper::STATUS_PUBLISHED : DefaultHelper::STATUS_DRAFT);
                $em->persist($article);
                break;
            default:
                // Set flash message
                $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
                return $this->redirectToRoute('dashboard_article_index');
        }

        // Update data
        $em->flush();

        // Set flash message
        $this->addFlash('success', sprintf($translator->trans('controller.success_multiple', [], 'messages'), $action === 'moderate' ? $translator->trans('controller.moderated', [], 'messages') : $translator->trans('controller.deleted', [], 'messages')));

        // Redirect to listing page
        return $this->redirectToRoute('dashboard_article_index');
    }
}