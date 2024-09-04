<?php

namespace App\Controller\Dashboard;

use App\Entity\Faq;
use App\Entity\FaqTranslations;
use App\Form\Type\FaqType;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class FAQController extends AbstractController
{
    #[Route('/dashboard/faqs', name: 'dashboard_faq_index')]
    public function index(): Response
    {
        return $this->render('dashboard/faq/index.html.twig');
    }

    #[Route('/dashboard/faq/create', name: 'dashboard_faq_create')]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader): Response
    {
        $language = $languageHelper->getDefaultLanguage();

        $faq = new Faq();
        $form = $this->createForm(FaqType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $faq->setUuid(Uuid::v4());

            $faqTranslation = new FaqTranslations();
            $faqTranslation->setFAQ($faq);
            $faqTranslation->setLanguage($language);
            $faqTranslation->setQuestions($form->get('question')->getData());
            $faqTranslation->setAnswer($form->get('answer')->getData());

            $em->persist($faq);
            $em->persist($faqTranslation);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully added a new faq.');
            return $this->redirectToRoute('dashboard_faq_index');
        }

        return $this->render('dashboard/faq/management.html.twig', [
            'form' => $form->createView(),
            'editMode' => false,
        ]);
    }

    #[Route('/dashboard/faq/{uuid}/edit', name: 'dashboard_faq_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid): Response
    {
        $faq = $em->getRepository(Faq::class)->findOneBy(['uuid' => $uuid]);
        if (null === $faq) return $this->redirectToRoute('dashboard_index');

        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $language = $languageHelper->getLanguageByLocale($locale);

        $faqTranslation = $em->getRepository(FaqTranslations::class)->findOneBy([
            'faq' => $faq,
            'language' => $language
        ]);

        if (null === $faqTranslation) {
            $faqTranslation = new FaqTranslations();
            $faqTranslation->setFAQ($faq);
            $faqTranslation->setLanguage($language);
        }

        $form = $this->createForm(FaqType::class, $faq, [
            'translation' => $faqTranslation
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $faq->setUuid(Uuid::v4());

            $faqTranslation = new FaqTranslations();
            $faqTranslation->setFAQ($faq);
            $faqTranslation->setLanguage($language);
            $faqTranslation->setQuestions($form->get('question')->getData());
            $faqTranslation->setAnswer($form->get('answer')->getData());

            $em->persist($faq);
            $em->persist($faqTranslation);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully added a new faq.');
            return $this->redirectToRoute('dashboard_faq_index');
        }

        return $this->render('dashboard/faq/management.html.twig', [
            'form' => $form->createView(),
            'entity' => $faq,
            'editMode' => true
        ]);
    }

    #[Route('/dashboard/faq/{uuid}/delete', name: 'dashboard_faq_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        $faq = $em->getRepository(Faq::class)->findOneBy(['uuid' => $uuid]);
        if (null === $faq) return $this->redirectToRoute('dashboard_index');

        $em->remove($faq);
        $em->flush();

        $this->addFlash('success', 'The faq has been successfully deleted');
        return $this->redirectToRoute('dashboard_faq_index');
    }
}
