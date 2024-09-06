<?php

namespace App\Controller\Dashboard;

use App\Entity\Article;
use App\Entity\Education;
use App\Entity\EducationRegistration;
use App\Entity\User;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard_index')]
    public function index(EntityManagerInterface $em, LanguageHelper $languageHelper, Request $request): Response
    {
        $educationRepository = $em->getRepository(Education::class);
        $educations = [
            'educations' => $educationRepository->findBy(['deletedAt' => null], ['createdAt' => "DESC"], 5),
            'educationsCount' => $educationRepository->findTotalCount(),
            'coursesCount' => $educationRepository->findTotalCount(Education::TYPE_COURSE),
            'workshopsCount' => $educationRepository->findTotalCount(Education::TYPE_WORKSHOP),
            'conventionsCount' => $educationRepository->findTotalCount(Education::TYPE_CONVENTION),
        ];

        $articleRepository = $em->getRepository(Article::class);
        $articlesCount = $articleRepository->findTotalCount();
        $articles = $articleRepository->findBy(['deletedAt' => null], ['createdAt' => "DESC"], 5);

        return $this->render('dashboard/default/index.html.twig', [
            'users' => $em->getRepository(User::class)->findTotalCount("ROLE_CLIENT"),
            'educations' => $educations,
            'articlesCount' => $articlesCount,
            'articles' => $articles,
            'chartData' => 23
        ]);
    }

    #[Route('/dashboard/report', name: 'dashboard_report_index')]
    public function report(EntityManagerInterface $em): Response
    {
        $educationYears = $em->getRepository(Education::class)->getDataYears();

        $years = $em->getRepository(EducationRegistration::class)->getUserYears();

        $combinedArray = array_merge($educationYears);
        $uniqueYears = array_unique($combinedArray);
        rsort($uniqueYears);

        $courses = $em->getRepository(Education::class)->findBy(['deletedAt' => null, 'type' => Education::TYPE_COURSE]);
        $workshops = $em->getRepository(Education::class)->findBy(['deletedAt' => null, 'type' => Education::TYPE_WORKSHOP]);

        return $this->render('dashboard/report/index.html.twig', [
            'page_title' => 'Reports',
            'dataYears' => $uniqueYears,
            'years' => $years,
            'courses' => $courses,
            'workshops' => $workshops,
        ]);
    }
}
