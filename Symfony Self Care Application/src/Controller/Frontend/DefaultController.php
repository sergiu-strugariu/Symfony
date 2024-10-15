<?php

namespace App\Controller\Frontend;

use App\Entity\Article;
use App\Entity\CategoryArticle;
use App\Entity\CategoryCare;
use App\Entity\CategoryCourse;
use App\Entity\CategoryJob;
use App\Entity\CategoryService;
use App\Entity\Company;
use App\Entity\Event;
use App\Entity\Job;
use App\Entity\MembershipPackage;
use App\Entity\Page;
use App\Entity\Payment;
use App\Entity\TrainingCourse;
use App\Entity\User;
use App\Entity\UserBillingData;
use App\Helper\BreadcrumbsHelper;
use App\Helper\FormValidatorHelper;
use App\Helper\LanguageHelper;
use App\Helper\MembershipHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(EntityManagerInterface $em): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "homepage"]);

        /** @var CategoryCare $categoriesCare */
        $categoriesCare = $em->getRepository(CategoryCare::class)->getCategories();

        /** @var CategoryService $categoriesCare */
        $categoriesService = $em->getRepository(CategoryService::class)->getCategories();

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'categoriesCare' => $categoriesCare,
            'categoriesService' => $categoriesService
        ]);
    }

    #[Route('/rezultate-search/{slug?}', name: 'app_search_result')]
    public function searchResults(EntityManagerInterface $em, Request $request, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "search"]);

        if (empty($request->get('type')) && empty($request->get('search'))) {
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('frontend/pages/search.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::SEARCH_BREADCRUMBS
        ]);
    }

    #[Route(path: '/creare-cont', name: 'app_create_account')]
    public function register(EntityManagerInterface $em): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "create-account"]);

        return $this->render('frontend/pages/create-account.html.twig', [
            'page' => $page
        ]);
    }

    #[Route('/linia-de-ajutor', name: 'app_helpline')]
    public function helpline(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'helpline']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::HELPLINE_BREADCRUMBS
        ]);
    }

    #[Route('/despre-noi', name: 'app_about_us')]
    public function aboutUs(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'about-us']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::ABOUTUS_BREADCRUMBS
        ]);
    }

    #[Route('/beneficii', name: 'app_benefits')]
    public function benefits(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'benefits']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::BENEFIT_BREADCRUMBS
        ]);
    }

    #[Route('/blog', name: 'app_blog')]
    public function blog(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "blog"]);

        /** @var CategoryArticle $categories */
        $categories = $em->getRepository(CategoryArticle::class)->getCategories();

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'categories' => $categories,
            'breadcrumbs' => $helper::BLOG_LISTING_BREADCRUMBS
        ]);
    }

    #[Route('/blog/{slug?}', name: 'app_blog_single')]
    public function singleBlog(EntityManagerInterface $em, BreadcrumbsHelper $helper, $slug): Response
    {
        $breadcrumbs = $helper::BLOG_SINGLE_BREADCRUMBS;
        $artRepository = $em->getRepository(Article::class);
        $locale = $this->getParameter('default_locale');

        /** @var Article $article */
        $article = $artRepository->getSingleArticleByParams($slug);

        // Check exist article
        if (!isset($article)) {
            return $this->redirectToRoute('app_blog');
        }

        $recommended = $artRepository->getArticles($article);
        $nextArticle = $artRepository->findNextPrevArticle($article);
        $prevArticle = $artRepository->findNextPrevArticle($article, 'DESC');

        $pageTitle = $article->getTranslation($locale)->getTitle();
        $breadcrumbs[] = [
            'name' => $pageTitle,
            'route' => null,
            'params' => []
        ];


        return $this->render('frontend/pages/article.html.twig', [
            'pageTitle' => $pageTitle,
            'article' => $article,
            'recommended' => $recommended,
            'nextArticle' => $nextArticle,
            'prevArticle' => $prevArticle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    #[Route('/camine', name: 'app_company')]
    public function companies(EntityManagerInterface $em, MembershipHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'companies']);

        $limit = 4;
        $companies = [];

        // All packages
        $packages = MembershipPackage::getPackages();

        // Exclude packages in this section
        $excludePackages = [MembershipPackage::PACKAGE_FREE];

        /** @var CategoryCare $categories */
        $categories = $em->getRepository(CategoryCare::class)->getCategories();

        // Parse and call by @package
        foreach ($packages as $package) {
            /**
             * Get items by category
             */
            $result = $em->getRepository(Company::class)->getCompaniesByType(Company::LOCATION_TYPE_CARE, null, 4, $package);

            // Store results in array by @package
            $companies[$package] = $result;
        }

        // Process companies by package and limit
        $recommended = $helper->filterDataByPackages($companies, $excludePackages, $limit);

        // Parse and increment entityLog
        $helper->parseEntityLog($recommended, Company::ENTITY_NAME);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'categories' => $categories,
            'recommended' => $recommended,
            'breadcrumbs' => BreadcrumbsHelper::COMPANIES_BREADCRUMBS
        ]);
    }

    #[Route('/camin/{slug?}', name: 'app_company_single')]
    public function singleCompany(EntityManagerInterface $em, MembershipHelper $helper, $slug): Response
    {
        $breadcrumbs = BreadcrumbsHelper::COMPANY_SINGLE_BREADCRUMBS;
        $companyRepository = $em->getRepository(Company::class);

        $limit = 4;
        $categoryRecommended = [];
        $countyRecommended = [];

        // All packages
        $packages = MembershipPackage::getPackages();

        // Exclude packages in this section
        $excludePackages = [MembershipPackage::PACKAGE_FREE];

        // Check exist slug
        if (empty($slug)) {
            return $this->redirectToRoute('app_company');
        }

        /** @var Company $company */
        $company = $companyRepository->getSingleCompanyBySlug($slug);

        // Check exist company
        if (empty($company)) {
            return $this->redirectToRoute('app_company');
        }

        // Parse and call by @package
        foreach ($packages as $package) {
            /**
             * Get items by category
             */
            $resultCategory = $companyRepository->getCompaniesByCategoryOrCounty($company, $package, true, false, $limit);

            /**
             * Get items by county
             */
            $resultCounty = $companyRepository->getCompaniesByCategoryOrCounty($company, $package, false, true, $limit);

            // Store results in array by @package
            $categoryRecommended[$package] = $resultCategory;
            $countyRecommended[$package] = $resultCounty;
        }

        // Process companies by package and limit
        $categoryRows = $helper->filterDataByPackages($categoryRecommended, $excludePackages, $limit);
        $countyRows = $helper->filterDataByPackages($countyRecommended, $excludePackages, $limit);

        // Parse and increment entityLog
        $helper->parseEntityLog($categoryRows, Company::ENTITY_NAME);
        $helper->parseEntityLog($countyRows, Company::ENTITY_NAME);

        // Update breadcrumb page
        $breadcrumbs[] = [
            'name' => $company->getName(),
            'route' => null,
            'params' => []
        ];

        return $this->render('frontend/pages/company.html.twig', [
            'breadcrumbs' => $breadcrumbs,
            'company' => $company,
            'galleryImage' => $company->getCompanyGalleries()->first(),
            'category' => $company->getCategoryCares()->first(),
            'countyRecommended' => $countyRows,
            'categoryRecommended' => $categoryRows
        ]);
    }

    #[Route('/camin/trimite-recenzie/{slug?}', name: 'app_company_review')]
    public function review(EntityManagerInterface $em, $slug): Response
    {
        $company = $em->getRepository(Company::class)->findOneBy([
            'slug' => $slug,
            'status' => Company::STATUS_PUBLISHED,
            'locationType' => Company::LOCATION_TYPE_CARE
        ]);

        // Check exist article
        if (!isset($company)) {
            return $this->redirectToRoute('app_company');
        }

        return $this->render('frontend/pages/review.html.twig', [
            'company' => $company
        ]);
    }

    #[Route('/trimite-recenzie', name: 'app_send_review')]
    public function sendReview(TranslatorInterface $translator): Response
    {
        return $this->render('frontend/pages/send-review.html.twig', [
            'pageTitle' => $translator->trans('review.review_btn', [], 'messages'),
        ]);
    }

    #[Route('/furnizori', name: 'app_provider')]
    public function providers(EntityManagerInterface $em, MembershipHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'providers']);

        $limit = 4;
        $companies = [];

        // All packages
        $packages = MembershipPackage::getPackages();

        // Exclude packages in this section
        $excludePackages = [MembershipPackage::PACKAGE_FREE];

        $categories = $em->getRepository(CategoryService::class)->getCategories();

        // Parse and call by @package
        foreach ($packages as $package) {
            /**
             * Get items by category
             */
            $result = $em->getRepository(Company::class)->getCompaniesByType(Company::LOCATION_TYPE_PROVIDER, null, 4, $package);

            // Store results in array by @package
            $companies[$package] = $result;
        }

        // Process companies by package and limit
        $recommended = $helper->filterDataByPackages($companies, $excludePackages, $limit);

        // Parse and increment entityLog
        $helper->parseEntityLog($recommended, Company::ENTITY_NAME);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'categories' => $categories,
            'recommended' => $recommended,
            'breadcrumbs' => BreadcrumbsHelper::PROVIDERS_BREADCRUMBS
        ]);
    }

    #[Route('/furnizor/{slug?}', name: 'app_provider_single')]
    public function singleProvider(EntityManagerInterface $em, MembershipHelper $helper, $slug): Response
    {
        $breadcrumbs = BreadcrumbsHelper::PROVIDER_SINGLE_BREADCRUMBS;
        $providerRepository = $em->getRepository(Company::class);

        $limit = 4;
        $categoryRecommended = [];
        $countyRecommended = [];

        // All packages
        $packages = MembershipPackage::getPackages();

        // Exclude packages in this section
        $excludePackages = [MembershipPackage::PACKAGE_FREE];

        // Check exist slug
        if (empty($slug)) {
            return $this->redirectToRoute('app_provider');
        }

        /** @var Company $provider */
        $provider = $providerRepository->getSingleCompanyBySlug($slug, Company::LOCATION_TYPE_PROVIDER);

        // Check exist company
        if (empty($provider)) {
            return $this->redirectToRoute('app_provider');
        }


        // Parse and call by @package
        foreach ($packages as $package) {
            /**
             * Get items by category
             */
            $resultCategory = $providerRepository->getCompaniesByCategoryOrCounty($provider, $package, true, false, $limit);

            /**
             * Get items by county
             */
            $resultCounty = $providerRepository->getCompaniesByCategoryOrCounty($provider, $package, false, true, $limit);

            // Store results in array by @package
            $categoryRecommended[$package] = $resultCategory;
            $countyRecommended[$package] = $resultCounty;
        }

        // Process companies by package and limit
        $categoryRows = $helper->filterDataByPackages($categoryRecommended, $excludePackages, $limit);
        $countyRows = $helper->filterDataByPackages($countyRecommended, $excludePackages, $limit);

        // Parse and increment entityLog
        $helper->parseEntityLog($categoryRows, Company::ENTITY_NAME);
        $helper->parseEntityLog($countyRows, Company::ENTITY_NAME);

        $breadcrumbs[] = [
            'name' => $provider->getName(),
            'route' => null,
            'params' => []
        ];

        return $this->render('frontend/pages/provider.html.twig', [
            'breadcrumbs' => $breadcrumbs,
            'provider' => $provider,
            'category' => $provider->getCategoryServices()->first(),
            'countyRecommended' => $countyRows,
            'categoryRecommended' => $categoryRows
        ]);
    }

    #[Route('/joburi', name: 'app_jobs')]
    public function jobs(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "jobs"]);

        /** @var CategoryJob $categories */
        $categories = $em->getRepository(CategoryJob::class)->getCategories();

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'jobTypes' => Job::getJobTypes(),
            'categories' => $categories,
            'breadcrumbs' => $helper::JOB_LISTING_BREADCRUMBS
        ]);
    }

    #[Route('/job/{slug?}', name: 'app_job_single')]
    public function singleJob(EntityManagerInterface $em, BreadcrumbsHelper $helper, LanguageHelper $languageHelper, $slug): Response
    {
        $breadcrumbs = $helper::JOB_SINGLE_BREADCRUMBS;
        $jobRepo = $em->getRepository(Job::class);

        $locale = $this->getParameter('default_locale');
        $language = $languageHelper->getLanguageByLocale($locale);

        // Check exist slug
        if (!isset($slug)) {
            return $this->redirectToRoute('app_jobs');
        }

        /**
         * Get job by slug
         * @var Job $job
         */
        $job = $jobRepo->getSingleJobByParams($slug);

        // Check exist article
        if (!isset($job)) {
            return $this->redirectToRoute('app_jobs');
        }

        /**
         * Get jobs by  language and category
         * @var Job $recommended
         */
        $recommended = $jobRepo->getRecommendedJobs($language, $job, 3);

        /**
         * Get courses by language and course
         * @var TrainingCourse $recommendedCourses
         */
        $recommendedCourses = $em->getRepository(TrainingCourse::class)->getRecommendedCourses($language, null, 4);

        $pageTitle = $job->getTranslation($locale)->getTitle();
        $breadcrumbs[] = [
            'name' => $pageTitle,
            'route' => null,
            'params' => []
        ];

        return $this->render('frontend/pages/job.html.twig', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'recommended' => $recommended,
            'recommendedCourses' => $recommendedCourses,
            'job' => $job
        ]);
    }

    #[Route('/cursuri', name: 'app_courses')]
    public function courses(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "courses"]);

        /** @var CategoryCourse $categories */
        $categories = $em->getRepository(CategoryCourse::class)->getCategories();

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'categories' => $categories,
            'formats' => TrainingCourse::getFormats(),
            'breadcrumbs' => $helper::COURSE_LISTING_BREADCRUMBS
        ]);
    }

    #[Route('/curs/{slug?}', name: 'app_course_single')]
    public function singleCourse(EntityManagerInterface $em, BreadcrumbsHelper $helper, LanguageHelper $languageHelper, $slug): Response
    {
        $breadcrumbs = $helper::COURSE_SINGLE_BREADCRUMBS;
        $courseRepo = $em->getRepository(TrainingCourse::class);

        $locale = $this->getParameter('default_locale');
        $language = $languageHelper->getLanguageByLocale($locale);

        // Check exist slug
        if (!isset($slug)) {
            return $this->redirectToRoute('app_courses');
        }

        /**
         * Get course by slug
         * @var TrainingCourse $course
         */
        $course = $courseRepo->getSingleCourseByParams($slug);

        // Check exist article
        if (!isset($course)) {
            return $this->redirectToRoute('app_courses');
        }

        /**
         * Get jobs by language
         * @var Job $recommended
         */
        $recommended = $em->getRepository(Job::class)->getRecommendedJobs($language, null, 3);

        /**
         * Get courses by language and course
         * @var TrainingCourse $recommendedCourses
         */
        $recommendedCourses = $courseRepo->getRecommendedCourses($language, $course, 4);


        $pageTitle = $course->getTranslation($locale)->getTitle();
        $breadcrumbs[] = [
            'name' => $pageTitle,
            'route' => null,
            'params' => []
        ];

        return $this->render('frontend/pages/course.html.twig', [
            'pageTitle' => $pageTitle,
            'course' => $course,
            'recommended' => $recommended,
            'recommendedCourses' => $recommendedCourses,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    #[Route('/evenimente', name: 'app_events')]
    public function events(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'events']);

        /** @var Event $years */
        $years = $em->getRepository(Event::class)->getYears();

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'years' => $years,
            'breadcrumbs' => $helper::EVENT_LISTING_BREADCRUMBS
        ]);
    }

    #[Route('/eveniment/{slug?}', name: 'app_event_single')]
    public function singleEvent(EntityManagerInterface $em, $slug): Response
    {
        $locale = $this->getParameter('default_locale');

        // Check exist slug
        if (!isset($slug)) {
            return $this->redirectToRoute('app_events');
        }

        /**
         * Get event by @slug
         * @var Event $event
         */
        $event = $em->getRepository(Event::class)->getSingleData($slug);

        // Check exist article
        if (empty($event)) {
            return $this->redirectToRoute('app_events');
        }

        return $this->render('frontend/pages/event.html.twig', [
            'pageTitle' => $event->getTranslation($locale)->getTitle(),
            'event' => $event,
        ]);
    }

    #[Route('/pachete-beneficii', name: 'app_benefit_packages')]
    public function benefitPackages(EntityManagerInterface $em, BreadcrumbsHelper $helper): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'benefit-packages']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'breadcrumbs' => $helper::BENEFIT_PACKAGES_BREADCRUMBS
        ]);
    }

    #[Route('/pachete', name: 'app_packages')]
    public function packages(EntityManagerInterface $em): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'packages']);

        /** @var MembershipPackage $packages */
        $packages = $em->getRepository(MembershipPackage::class)->getAllPackages();

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page,
            'packages' => $packages
        ]);
    }

    #[Route('/detalii-comanda/pachet/{slug}', name: 'app_comand_detail')]
    public function comandDetailPackage(EntityManagerInterface $em, Request $request, FormValidatorHelper $validatorHelper, TranslatorInterface $translator, $slug): Response
    {
        $billingRepository = $em->getRepository(UserBillingData::class);

        /** @var User $user */
        $user = $this->getUser();

        /**
         * Get package by @slug
         * @var MembershipPackage $package
         */
        $package = $em->getRepository(MembershipPackage::class)->findOneBy(['slug' => $slug]);

        // Check exist package
        if (empty($package) || empty($user) || $package->getSlug() === MembershipPackage::PACKAGE_FREE) {
            return $this->redirectToRoute('app_packages');
        }

        /**
         * Get favorites @user billings
         * @var UserBillingData $billings
         */
        $billings = $billingRepository->findBy(['user' => $user], ['isFavorite' => 'DESC']);

        // Get method and validate fields
        if ($request->isMethod('POST')) {
            // Retrieve form data from request
            $formData = $request->request->all();

            // monthly and yearly
            $packagePlan = $formData['selectedPackage'];

            /**
             * Get billing by @uuid
             * @var UserBillingData $billing
             */
            $billing = $billingRepository->findOneBy(['uuid' => $formData['billingDetail']]);

            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            // Check errors
            if ($validate['checkErrors'] || empty($billing) || !in_array($packagePlan, MembershipPackage::getPlans())) {
                // Set flash message and redirect
                $this->addFlash('danger', $translator->trans('form.default.default_all_field_required', [], 'messages'));
                return $this->redirectToRoute('app_comand_detail', array_filter([
                    'slug' => $slug,
                    'packagePlan' => $packagePlan
                ]));
            }

            // Insert new payment
            $payment = new Payment();
            $payment->setMembershipPackage($package);
            $payment->setUserBillingData($billing);
            $payment->setUser($user);
            $payment->setStatus(Payment::PAYMENT_STATUS_PENDING);
            $payment->setPrice($packagePlan === MembershipPackage::YEARLY ? $package->getYearlyPrice() : $package->getPrice());
            $payment->setPlan($packagePlan);

            // Persist and save
            $em->persist($payment);
            $em->flush();

            // Redirect to pay
            return $this->redirectToRoute('app_payment', ['uuid' => $payment->getUuid()]);
        }

        return $this->render('frontend/pages/package-order.html.twig', [
            'package' => $package,
            'user' => $user,
            'billings' => $billings
        ]);
    }

    #[Route('/trimite-feedback', name: 'app_feedback')]
    public function feedback(): Response
    {
        return $this->render('frontend/pages/feedback.html.twig');
    }

    #[Route('/politica-de-confidentialitate', name: 'app_privacy')]
    public function privacy(EntityManagerInterface $em): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'privacy-policy']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page
        ]);
    }

    #[Route('/termeni-si-conditii', name: 'app_terms')]
    public function terms(EntityManagerInterface $em): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'terms-and-conditions']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page
        ]);
    }

    #[Route('/politica-cookies', name: 'app_cookies')]
    public function cookies(EntityManagerInterface $em): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'cookies']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page
        ]);
    }

    #[Route('/404', name: 'app_404')]
    public function error404(EntityManagerInterface $em): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => '404']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page
        ]);
    }
}