<?php

namespace App\Controller\Dashboard;

use App\Entity\Article;
use App\Entity\Company;
use App\Entity\Favorite;
use App\Entity\Job;
use App\Entity\TrainingCourse;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard_index')]
    public function index(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $totalCares = $em->getRepository(Company::class)->countCompanies($user);
        $totalProviders = $em->getRepository(Company::class)->countCompanies($user, Company::LOCATION_TYPE_PROVIDER);
        $totalJobs = $em->getRepository(Job::class)->countJobs($user);
        $totalCourses = $em->getRepository(TrainingCourse::class)->countTrainingCourse($user);
        $totalArticles = $em->getRepository(Article::class)->countArticles($user);

        $years = $em->getRepository(User::class)->getUserYears();
        $users = $em->getRepository(User::class)->getUsers();

        // All years
        $articleYears = $em->getRepository(Article::class)->getDataYears();
        $jobYears = $em->getRepository(Job::class)->getDataYears();
        $courseYears = $em->getRepository(TrainingCourse::class)->getDataYears();
        $careYears = $em->getRepository(Company::class)->getDataYears();
        $providerYears = $em->getRepository(Company::class)->getDataYears(Company::LOCATION_TYPE_PROVIDER);

        // Combine all arrays
        $combinedArray = array_merge($articleYears, $jobYears, $courseYears, $careYears, $providerYears);

        // Remove duplicates
        $uniqueYears = array_unique($combinedArray);

        // Sort the array in descending order
        rsort($uniqueYears);

        return $this->render('dashboard/default/index.html.twig', [
            'totalCares' => $totalCares,
            'totalProviders' => $totalProviders,
            'totalJobs' => $totalJobs,
            'totalCourses' => $totalCourses,
            'totalArticles' => $totalArticles,
            'years' => $years,
            'dataYears' => $uniqueYears,
            'users' => $users
        ]);
    }

    #[Route('/dashboard/my-account', name: 'dashboard_my_account')]
    public function profile(): Response
    {
        return $this->render('dashboard/default/my-account.html.twig', []);
    }

    #[Route('/dashboard/favorites', name: 'dashboard_favorites')]
    public function favorites(): Response
    {
        return $this->render('dashboard/default/favorites.html.twig', [
            'types' => Favorite::getFavoriteTypes()
        ]);
    }

    #[Route('/dashboard/my-plan', name: 'dashboard_my_plan')]
    public function myPlan(): Response
    {
        return $this->render('dashboard/default/my-plan.html.twig', []);
    }

    #[Route('/dashboard/cache/clear', name: 'dashboard_clear_cache')]
    public function clearCache(KernelInterface $kernel, TranslatorInterface $translator): Response
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'cache:clear',
            '--env' => $this->getParameter('app_env'),
            '--no-warmup' => true
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);


        // Set flash message
        $this->addFlash('success', $translator->trans('controller.clear_cache', [], 'messages'));
        return new RedirectResponse($this->generateUrl('dashboard_my_account'));
    }

    #[Route('/dashboard/rebuild-search', name: 'dashboard_rebuild_search')]
    public function rebuildSearch(KernelInterface $kernel, TranslatorInterface $translator): Response
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'fos:elastica:populate',
            '--env' => 'prod',
            '--no-warmup' => true
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);


        // Set flash message
        $this->addFlash('success', $translator->trans('controller.populate_content', [], 'messages'));
        return new RedirectResponse($this->generateUrl('dashboard_my_account'));
    }
}
