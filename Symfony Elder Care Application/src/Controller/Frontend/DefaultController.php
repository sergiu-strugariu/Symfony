<?php

namespace App\Controller\Frontend;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Helper\BreadcrumbsHelper;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function index(EntityManagerInterface $em): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'home']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page
        ]);
    }

    /**
     * @Route("/about", name="app_about")
     */
    public function about(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'about']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::ABOUTUS_BREADCRUMBS
        ]);
    }

    /**
     * @Route("/contact", name="app_contact")
     */
    public function contact(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'contact']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::CONTACT_BREADCRUMBS

        ]);
    }

    /**
     * @Route("/services", name="app_services")
     */
    public function services(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'services']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::SERVICES_BREADCRUMBS
        ]);
    }

    /**
     * @Route("/terms", name="app_terms")
     */
    public function terms(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'terms']);

        return $this->render('frontend/pages/legal.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::TERMS_BREADCRUMBS
        ]);
    }

    /**
     * @Route("/policy", name="app_policy")
     */
    public function policy(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'policy']);

        return $this->render('frontend/pages/legal.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::POLICY_BREADCRUMBS
        ]);
    }

    /**
     * @Route("/cookies", name="app_cookies")
     */
    public function cookies(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'cookies']);

        return $this->render('frontend/pages/legal.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::COOKIES_BREADCRUMBS
        ]);
    }
}
