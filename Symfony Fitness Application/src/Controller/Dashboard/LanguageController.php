<?php

namespace App\Controller\Dashboard;

use App\Entity\Language;
use App\Form\Type\LanguageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LanguageController extends AbstractController
{
    #[Route('/dashboard/languages', name: 'dashboard_language_index')]
    public function index(): Response
    {
        return $this->render('dashboard/language/index.html.twig');
    }

    #[Route('/dashboard/language/create', name: 'dashboard_language_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $language = new Language();

        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($language);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully added a new language.');
            return $this->redirectToRoute('dashboard_language_index');
        }

        return $this->render('dashboard/language/management.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/language/{locale}/edit', name: 'dashboard_language_edit')]
    public function edit(Request $request, EntityManagerInterface $em, $locale): Response
    {
        $language = $em->getRepository(Language::class)->findOneBy(['locale' => $locale]);
        if (null === $language) return $this->redirectToRoute('dashboard_index');

        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($language);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully added a new language.');
            return $this->redirectToRoute('dashboard_language_index');
        }

        return $this->render('dashboard/language/management.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/language/{locale}/delete', name: 'dashboard_language_delete')]
    public function delete(EntityManagerInterface $em, $locale): Response
    {
        if ($locale == $this->getParameter('default_locale')) {
            $this->addFlash('danger', "You can't delete the default language.");
            return $this->redirectToRoute('dashboard_language_index');
        }

        $language = $em->getRepository(Language::class)->findOneBy(['locale' => $locale]);
        if (null === $language) return $this->redirectToRoute('dashboard_index');

        $language->setDeletedAt(new \DateTime());
        $em->persist($language);
        $em->flush();

        $this->addFlash('success', 'The language has been successfully deleted');
        return $this->redirectToRoute('dashboard_language_index');
    }
}
