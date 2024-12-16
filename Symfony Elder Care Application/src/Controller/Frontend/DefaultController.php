<?php

namespace App\Controller\Frontend;

use App\Entity\MembershipPackage;
use App\Entity\Page;
use App\Helper\MembershipHelper;
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
            'page' => $page,
            'breadcrumbs' => []
        ]);
    }

    /**
     * @Route("/despre-noi", name="app_about_us")
     */
    public function aboutUs(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
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
     * @Route("/functionalitati", name="app_functions")
     */
    public function functions(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'services']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::SERVICES_BREADCRUMBS
        ]);
    }

    /**
     * @Route("/pachete", name="app_packages")
     */
    public function packages(EntityManagerInterface $em, BreadcrumbsHelper $helper, MembershipHelper $membershipHelper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'packages']);

        /** @var MembershipPackage $packages */
        $packages = $em->getRepository(MembershipPackage::class)->getAllPackages();

        // Parse packages by @slug
        $modules = $membershipHelper->parseResponse($packages);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'packages' => $packages,
            'modules' => $modules,
            'breadcrumbs' => $helper::PACKAGES_BREADCRUMBS
        ]);
    }

    /**
     * @Route("/detalii-comanda/pachet/{slug}", name="app_comand_detail")
     */
    public function comandDetail(EntityManagerInterface $em): Response
    {
        // TODO: Update machine name, this for testing redirect

        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'contact']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'breadcrumbs' => []
        ]);
    }

    /**
     * @Route("/termeni-si-conditii", name="app_terms")
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
     * @Route("/politica-de-confidentialitate", name="app_privacy_policy")
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
     * @Route("/politica-cookies", name="app_cookies")
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

    /**
     * @Route("/404", name="app_404")
     */
    public function error404(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => '404']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'breadcrumbs' => []
        ]);
    }
}