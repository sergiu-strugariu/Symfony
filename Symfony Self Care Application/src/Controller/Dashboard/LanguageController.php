<?php

namespace App\Controller\Dashboard;

use App\Entity\Language;
use App\Form\Type\LanguageFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class LanguageController extends AbstractController
{
    #[Route('/dashboard/language', name: 'dashboard_lang_index')]
    public function index(): Response
    {
        return $this->render('dashboard/language/index.html.twig');
    }

    #[Route('/dashboard/language/create', name: 'dashboard_lang_create')]
    public function create(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $lang = new Language();
        $form = $this->createForm(LanguageFormType::class, $lang);
        $form->handleRequest($request);

        // Validate data
        if ($form->isSubmitted() && $form->isValid()) {
            // Save data
            $em->persist($lang);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));
            return $this->redirectToRoute('dashboard_lang_index');
        }

        return $this->render('dashboard/language/actions.html.twig', [
            'pageTitle' => $translator->trans('controller.create_language', [], 'messages'),
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/language/{locale}/edit', name: 'dashboard_lang_edit')]
    public function edit(Request $request, EntityManagerInterface $em, $locale, TranslatorInterface $translator): Response
    {
        /**
         * Get lang by @locale
         * @var Language $lang
         */
        $lang = $em->getRepository(Language::class)->findOneBy(['locale' => $locale]);

        // Check exist @language
        if (null === $lang) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));

            return $this->redirectToRoute('dashboard_lang_index');
        }

        // Init form & handle request data
        $form = $this->createForm(LanguageFormType::class, $lang);
        $form->handleRequest($request);

        // Validate data
        if ($form->isSubmitted() && $form->isValid()) {
            // Save data
            $em->persist($lang);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages') . ' - ' . $lang->getName());
            return $this->redirectToRoute('dashboard_lang_index');
        }

        return $this->render('dashboard/language/actions.html.twig', [
            'pageTitle' =>  $translator->trans('controller.edit_language', [], 'messages'),
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/language/{locale}/delete', name: 'dashboard_language_delete')]
    public function delete(EntityManagerInterface $em, $locale, TranslatorInterface $translator): Response
    {
        if ($locale === $this->getParameter('default_locale')) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.error_cannot_delete_default_language', [], 'messages'));
            return $this->redirectToRoute('dashboard_lang_index');
        }

        /** @var Language $lang */
        $lang = $em->getRepository(Language::class)->findOneBy(['locale' => $locale]);

        if (!isset($lang)) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_lang_index');
        }

        // Update data
        $lang->setDeletedAt(new DateTime());
        $em->persist($lang);
        $em->flush();

        // Set flash message
        $this->addFlash('success', $translator->trans('controller.success_delete', [], 'messages'));

        // Redirect to listing page
        return $this->redirectToRoute('dashboard_lang_index');
    }
}
