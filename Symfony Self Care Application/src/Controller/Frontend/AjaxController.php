<?php

namespace App\Controller\Frontend;

use App\Entity\Article;
use App\Entity\CategoryArticle;
use App\Entity\CategoryCare;
use App\Entity\CategoryCourse;
use App\Entity\CategoryJob;
use App\Entity\CategoryService;
use App\Entity\CompanyReview;
use App\Entity\County;
use App\Entity\Company;
use App\Entity\Event;
use App\Entity\Favorite;
use App\Entity\Job;
use App\Entity\TrainingCourse;
use App\Entity\User;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use App\Helper\FormValidatorHelper;
use App\Helper\LanguageHelper;
use App\Helper\MailchimpAPIHelper;
use App\Helper\MailHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception as ExceptionAlias;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use GuzzleHttp\Exception\ClientException;

class AjaxController extends AbstractController
{
    #[Route('/ajax/get-counties', name: 'ajax_counties')]
    public function getCities(EntityManagerInterface $em): JsonResponse
    {
        $counties = $em->getRepository(County::class)->findCounties();

        return new JsonResponse([
            'status' => true,
            'counties' => $counties
        ]);
    }

    #[Route('/ajax/get-companies', name: 'ajax_get_company')]
    public function getCompaniesByType(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $locationType = $request->get('locationType', Company::LOCATION_TYPE_CARE);
        $limit = $request->get('limit', 7);
        $categorySlug = $request->get('categorySlug', '');
        $category = null;

        // Check exist type in array
        if (!isset($locationType) || !in_array($locationType, Company::getLocationTypes())) {
            return new JsonResponse([
                'status' => false,
                'rows' => [],
                'totalRows' => 0
            ]);
        }

        // Check exist slug in params
        if (!empty($categorySlug)) {
            $categoryClass = match ($locationType) {
                Company::LOCATION_TYPE_CARE => CategoryCare::class,
                Company::LOCATION_TYPE_PROVIDER => CategoryService::class,
            };

            $category = $em->getRepository($categoryClass)->findOneBy(['slug' => $categorySlug]);
        }

        /**
         * Get items
         * @var Company $companies
         */
        $companies = $em->getRepository(Company::class)->getCompaniesByType($locationType, $category, $locationType === Company::LOCATION_TYPE_CARE ? $limit : 4);

        /**
         * Get total items
         * @var Company $countCompanies
         */
        $countCompanies = $em->getRepository(Company::class)->getCompaniesByType($locationType, $category, $locationType === Company::LOCATION_TYPE_CARE ? $limit : 4, true);

        return new JsonResponse([
            'status' => true,
            'rows' => $companies,
            'totalRows' => $countCompanies
        ]);
    }

    #[Route('/ajax/get-articles', name: 'ajax_get_articles')]
    public function getArticles(EntityManagerInterface $em, Request $request, LanguageHelper $languageHelper): JsonResponse
    {
        $limit = $request->get('limit', 9);
        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $slug = $request->get('slug', '');
        $page = $request->get('page', 1);
        $category = null;

        // Check exist slug in params
        if (!empty($slug)) {
            /** @var CategoryArticle $category */
            $category = $em->getRepository(CategoryArticle::class)->findOneBy(['slug' => $slug]);
        }

        $language = $languageHelper->getLanguageByLocale($locale);
        $offset = ($page - 1) * $limit;

        // Get data articles by @filters
        $articles = $em->getRepository(Article::class)->getArticlesByFilters($language, $category, $limit, $offset);

        // Get total articles by @filters
        $countArticles = $em->getRepository(Article::class)->getArticlesByFilters($language, $category, $limit, $offset, true);

        // Calculate totalPage / limit
        $totalPages = ceil($countArticles / $limit);

        return new JsonResponse([
            'status' => true,
            'rows' => $articles,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    #[Route('/ajax/get-filter-companies', name: 'ajax_get_filter_companies')]
    public function getCompanies(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $companyRepo = $em->getRepository(Company::class);

        $locationType = $request->get('locationType', Company::LOCATION_TYPE_CARE);
        $limit = $request->get('limit', 10);
        $sortName = $request->get('sortName', 'id');
        $sortOrder = $request->get('sortOrder', 'DESC');

        $categorySlug = $request->get('categorySlug', '');
        $countyCode = $request->get('county', '');
        $page = $request->get('page', 1);

        $category = null;
        $county = null;

        // Check exist slug in params
        if (!empty($categorySlug)) {
            $categoryClass = match ($locationType) {
                Company::LOCATION_TYPE_CARE => CategoryCare::class,
                Company::LOCATION_TYPE_PROVIDER => CategoryService::class,
            };

            $category = $em->getRepository($categoryClass)->findOneBy(['slug' => $categorySlug]);
        }

        // Check exist county in params
        if (!empty($countyCode)) {
            /** @var County $county */
            $county = $em->getRepository(County::class)->findOneBy(['code' => $countyCode]);
        }

        $offset = ($page - 1) * $limit;

        // Get data companies by @filters
        $companies = $companyRepo->getCompaniesByFilters(
            $category,
            $county,
            empty($sortName) ? 'id' : $sortName,
            empty($sortOrder) ? 'DESC' : $sortOrder,
            $locationType,
            $limit,
            $offset
        );

        // Get total companies by @filters
        $countCompanies = $companyRepo->getCompaniesByFilters(
            $category,
            $county,
            empty($sortName) ? 'id' : $sortName,
            empty($sortOrder) ? 'DESC' : $sortOrder,
            $locationType,
            $limit,
            $offset,
            true
        );

        // Calculate totalPage / limit
        $totalPages = ceil($countCompanies / $limit);

        return new JsonResponse([
            'status' => true,
            'rows' => $companies,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    #[Route('/ajax/get-filter-events', name: 'ajax_get_filter_events')]
    public function getEvents(EntityManagerInterface $em, Request $request, LanguageHelper $languageHelper): JsonResponse
    {
        $eventRepo = $em->getRepository(Event::class);

        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $limit = $request->get('limit', 10);
        $sortName = $request->get('sortName', 'id');
        $sortOrder = $request->get('sortOrder', 'DESC');

        $status = $request->get('eventStatus', '');
        $year = $request->get('year', '');

        $page = $request->get('page', 1);

        $offset = ($page - 1) * $limit;
        $language = $languageHelper->getLanguageByLocale($locale);

        // Get data companies by @filters
        $events = $eventRepo->getEventsByFilters(
            $language,
            empty($sortName) ? 'id' : $sortName,
            empty($sortOrder) ? 'DESC' : $sortOrder,
            $limit,
            $offset,
            false,
            $status,
            $year,
        );

        // Get total companies by @filters
        $countEvents = $eventRepo->getEventsByFilters(
            $language,
            empty($sortName) ? 'id' : $sortName,
            empty($sortOrder) ? 'DESC' : $sortOrder,
            $limit,
            $offset,
            true,
            $status,
            $year,
        );

        // Calculate totalPage / limit
        $totalPages = ceil($countEvents / $limit);

        return new JsonResponse([
            'status' => true,
            'rows' => $events,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/ajax/send-company-details-form', name: 'ajax_send_company_details_form')]
    public function companyDetailsForm(Request $request, FormValidatorHelper $validatorHelper, DefaultHelper $helper, TranslatorInterface $translator, MailHelper $mail): JsonResponse
    {
        // Init variables
        $validate = ['checkErrors' => false, 'errors' => []];
        $emailSend = false;

        // Retrieve form data from request
        $formData = $request->request->all();

        // Process form submission
        if ($request->isMethod('POST')) {
            // Validate recaptcha
            if ($helper->captchaVerify($request->get('g-recaptcha-response'))) {
                return new JsonResponse([
                    'status' => false,
                    'errors' => [],
                    'message' => $translator->trans('form.messages.form_recaptcha', [], 'messages')
                ]);
            }

            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            // Check errors
            if (!$validate['checkErrors']) {
                // Send email to @appEmail
                $emailSend = $mail->sendMail(
                    $this->getParameter('app_email'),
                    'Cerere detaliu',
                    'frontend/emails/company-details.html.twig', $formData
                );
            }
        }

        return new JsonResponse([
            'status' => $emailSend,
            'errors' => $validate['errors'],
            'message' => $translator->trans($emailSend ? 'form.messages.form_details_success' : 'form.messages.form_details_error', [], 'messages')
        ]);
    }

    #[Route('/ajax/get-recommended-jobs', name: 'ajax_get_recommended_jobs')]
    public function getRecommendedJobs(EntityManagerInterface $em, Request $request, LanguageHelper $languageHelper): JsonResponse
    {
        $jobRepo = $em->getRepository(Job::class);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 6);
        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $offset = ($page - 1) * $limit;

        $language = $languageHelper->getLanguageByLocale($locale);

        /** @var Job $jobs */
        $jobs = $jobRepo->getRecommendedJobs($language, null, $limit, $offset);

        /** @var Job $countJobs */
        $countJobs = $jobRepo->getRecommendedJobs($language, null, $limit, $offset, true);

        // Calculate totalPage / limit
        $totalPages = ceil($countJobs / $limit);

        return new JsonResponse([
            'status' => true,
            'rows' => $jobs,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * @throws ExceptionAlias
     */
    #[Route('/ajax/get-listing-jobs', name: 'ajax_get_listing_jobs')]
    public function getListingJobs(EntityManagerInterface $em, Request $request, LanguageHelper $languageHelper): JsonResponse
    {
        $jobRepo = $em->getRepository(Job::class);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $locale = $request->get('locale', $this->getParameter('default_locale'));

        $sortName = $request->get('sortName', 'id');
        $sortOrder = $request->get('sortOrder', 'DESC');

        $categorySlug = $request->get('categorySlug', '');

        $countyCode = $request->get('county', '');
        $contractType = $request->get('contractType', '');

        $getCategory = null;
        $county = null;

        $offset = ($page - 1) * $limit;

        $language = $languageHelper->getLanguageByLocale($locale);

        if (!empty($categorySlug)) {
            /** @var CategoryJob $getCategory */
            $getCategory = $em->getRepository(CategoryJob::class)->findOneBy(['slug' => $categorySlug]);
        }

        // Check exist county in params
        if (!empty($countyCode)) {
            /** @var County $county */
            $county = $em->getRepository(County::class)->findOneBy(['code' => $countyCode]);
        }

        /** @var Job $jobs */
        $jobs = $jobRepo->getJobsByFilters(
            $language,
            $getCategory,
            $county,
            $sortName,
            $sortOrder,
            $contractType,
            $limit,
            $offset
        );

        /** @var Job $countJobs */
        $countJobs = $jobRepo->getJobsByFilters(
            $language,
            $getCategory,
            $county,
            $sortName,
            $sortOrder,
            $contractType,
            $limit,
            $offset,
            true
        );

        // Calculate totalPage / limit
        $totalPages = ceil($countJobs / $limit);

        return new JsonResponse([
            'status' => true,
            'rows' => $jobs,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ExceptionAlias
     */
    #[Route('/ajax/job-apply', name: 'ajax_job_apply')]
    public function jobApplyForm(EntityManagerInterface $em, Request $request, FormValidatorHelper $validatorHelper, DefaultHelper $helper, TranslatorInterface $translator, MailHelper $mail, FileUploader $uploader): JsonResponse
    {
        // Init variables
        $validate = ['checkErrors' => false, 'errors' => []];
        $emailSend = false;

        // Retrieve form data from request
        $formData = $request->request->all();
        $formData['fileCv'] = $request->files->get('fileCv');

        // Process form submission
        if ($request->isMethod('POST')) {
            // Validate recaptcha
            if ($helper->captchaVerify($request->get('g-recaptcha-response'))) {
                return new JsonResponse([
                    'status' => false,
                    'errors' => [],
                    'message' => $translator->trans('form.messages.form_recaptcha', [], 'messages')
                ]);
            }

            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            /**
             * Get company by @slug
             * @var Company $company
             */
            $company = $em->getRepository(Company::class)->findOneBy(['slug' => $formData['companySlug']]);

            // Check errors and exist company
            if (!$validate['checkErrors'] && isset($company)) {
                $formData['companySlug'] = $company->getName();

                // Upload company file
                $uploadFile = $uploader->uploadFile(
                    $formData['fileCv'],
                    null,
                    $this->getParameter('app_temporary_path')
                );

                if ($uploadFile['success']) {
                    // Get cv path
                    $filePath = $this->getParameter('cloudflare_path') . $this->getParameter('app_temporary_path') . $uploadFile['fileName'];

                    // Send email to @company and attach cv
                    $emailSend = $mail->sendMail(
                        $company->getEmail(),
                        $translator->trans('jobs.subject_mail_job', [], 'messages'),
                        'frontend/emails/job-apply.html.twig',
                        $formData,
                        [$filePath]
                    );

                    // Remove cv file
                    $uploader->removeFile($this->getParameter('app_temporary_path'), $uploadFile['fileName']);
                }
            }
        }

        return new JsonResponse([
            'status' => $emailSend,
            'errors' => $validate['errors'],
            'message' => $translator->trans($emailSend ? 'form.messages.form_details_success' : 'form.default.required', [], 'messages')
        ]);
    }

    #[Route('/ajax/get-recommended-courses', name: 'ajax_get_recommended_courses')]
    public function getRecommendedCourses(EntityManagerInterface $em, Request $request, LanguageHelper $languageHelper): JsonResponse
    {
        $courseRepo = $em->getRepository(TrainingCourse::class);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 6);
        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $offset = ($page - 1) * $limit;

        $language = $languageHelper->getLanguageByLocale($locale);

        /** @var TrainingCourse $courses */
        $courses = $courseRepo->getRecommendedCourses($language, null, $limit, $offset);

        /** @var TrainingCourse $countJobs */
        $countCourse = $courseRepo->getRecommendedCourses($language, null, $limit, $offset, true);

        // Calculate totalPage / limit
        $totalPages = ceil($countCourse / $limit);

        return new JsonResponse([
            'status' => true,
            'rows' => $courses,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * @throws ExceptionAlias
     */
    #[Route('/ajax/get-listing-courses', name: 'ajax_get_listing_courses')]
    public function getListingCourses(EntityManagerInterface $em, Request $request, LanguageHelper $languageHelper): JsonResponse
    {
        $courseRepo = $em->getRepository(TrainingCourse::class);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $locale = $request->get('locale', $this->getParameter('default_locale'));

        $sortName = $request->get('sortName', 'id');
        $sortOrder = $request->get('sortOrder', 'DESC');

        $categorySlug = $request->get('categorySlug', '');

        $countyCode = $request->get('county', '');
        $format = $request->get('format', '');

        $getCategory = null;
        $county = null;

        $offset = ($page - 1) * $limit;

        $language = $languageHelper->getLanguageByLocale($locale);

        if (!empty($categorySlug)) {
            /** @var CategoryCourse $getCategory */
            $getCategory = $em->getRepository(CategoryCourse::class)->findOneBy(['slug' => $categorySlug]);
        }

        // Check exist county in params
        if (!empty($countyCode)) {
            /** @var County $county */
            $county = $em->getRepository(County::class)->findOneBy(['code' => $countyCode]);
        }

        /** @var TrainingCourse $courses */
        $courses = $courseRepo->getCourseByFilters(
            $language,
            $getCategory,
            $county,
            $sortName,
            $sortOrder,
            $format,
            $limit,
            $offset
        );

        /** @var TrainingCourse $countCourse */
        $countCourse = $courseRepo->getCourseByFilters(
            $language,
            $getCategory,
            $county,
            $sortName,
            $sortOrder,
            $format,
            $limit,
            $offset,
            true
        );

        // Calculate totalPage / limit
        $totalPages = ceil($countCourse / $limit);

        return new JsonResponse([
            'status' => true,
            'rows' => $courses,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ExceptionAlias
     */
    #[Route('/ajax/course-apply', name: 'ajax_course_apply')]
    public function courseApplyForm(EntityManagerInterface $em, Request $request, FormValidatorHelper $validatorHelper, DefaultHelper $helper, TranslatorInterface $translator, MailHelper $mail): JsonResponse
    {
        // Init variables
        $validate = ['checkErrors' => false, 'errors' => []];
        $emailSend = false;
        $recaptcha = $request->get('g-recaptcha-response');

        // Retrieve form data from request
        $formData = $request->request->all();
        $formData['privacy'] = $formData['privacy'] === 'on';

        // Process form submission
        if ($request->isMethod('POST')) {
            // Validate recaptcha
            if ($helper->captchaVerify($recaptcha)) {
                return new JsonResponse([
                    'status' => false,
                    'errors' => [],
                    'message' => $translator->trans('form.messages.form_recaptcha', [], 'messages')
                ]);
            }

            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            /**
             * Get company by @slug
             * @var Company $company
             */
            $company = $em->getRepository(Company::class)->findOneBy(['slug' => $formData['companySlug']]);

            // Check errors and exist company
            if (!$validate['checkErrors'] && isset($company)) {
                // Remove email key
                $formData['companySlug'] = $company->getName();

                // Send email to @company
                $emailSend = $mail->sendMail(
                    $company->getEmail(),
                    $translator->trans('courses.subject_mail', [], 'messages'),
                    'frontend/emails/course-apply.html.twig',
                    $formData
                );

            }
        }

        return new JsonResponse([
            'status' => $emailSend,
            'errors' => $validate['errors'],
            'message' => $translator->trans($emailSend ? 'form.messages.form_details_success' : 'form.default.required', [], 'messages')
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ExceptionAlias
     */
    #[Route('/ajax/send-feedback', name: 'ajax_send_feedback')]
    public function sendFeedback(Request $request, FormValidatorHelper $validatorHelper, DefaultHelper $helper, TranslatorInterface $translator, MailHelper $mail, FileUploader $uploader): JsonResponse
    {
        // Init variables
        $validate = ['checkErrors' => false, 'errors' => []];
        $emailSend = false;
        $recaptcha = $request->get('g-recaptcha-response');

        // Retrieve form data from request
        $formData = $request->request->all();
        $formData['fileName'] = $request->files->get('fileName');

        // Process form submission
        if ($request->isMethod('POST')) {
            // Validate recaptcha
            if ($helper->captchaVerify($recaptcha)) {
                return new JsonResponse([
                    'status' => false,
                    'errors' => [],
                    'message' => $translator->trans('form.messages.form_recaptcha', [], 'messages')
                ]);
            }

            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            // Check errors and exist company
            if (!$validate['checkErrors']) {
                // Upload company file
                $uploadFile = $uploader->uploadFile(
                    $formData['fileName'],
                    null,
                    $this->getParameter('app_temporary_path')
                );

                if ($uploadFile['success']) {
                    // Get cv path
                    $filePath = $this->getParameter('cloudflare_path') . $this->getParameter('app_temporary_path') . $uploadFile['fileName'];

                    // Send email to @company and attach cv
                    $emailSend = $mail->sendMail(
                        $this->getParameter('app_email'),
                        $translator->trans('common.feedback', [], 'messages'),
                        'frontend/emails/feedback-apply.html.twig',
                        $formData,
                        [$filePath]
                    );

                    // Remove cv file
                    $uploader->removeFile($this->getParameter('app_temporary_path'), $uploadFile['fileName']);
                }
            }
        }

        return new JsonResponse([
            'status' => $emailSend,
            'errors' => $validate['errors'],
            'message' => $translator->trans($emailSend ? 'form.messages.form_details_success' : 'form.default.required', [], 'messages')
        ]);
    }

    /**
     * @throws ExceptionAlias
     * @throws TransportExceptionInterface
     */
    #[Route('/ajax/send-review', name: 'ajax_send_review')]
    public function sendReview(EntityManagerInterface $em, Request $request, FormValidatorHelper $validatorHelper, DefaultHelper $helper, TranslatorInterface $translator, MailHelper $mail): JsonResponse
    {
        // Init variables
        $validate = ['checkErrors' => false, 'errors' => []];
        $recaptcha = $request->get('g-recaptcha-response');

        // Retrieve form data from request
        $formData = $request->request->all();
        $formData['nameAgree'] = $formData['nameAgree'] === 'on';
        $formData['myRatingAgree'] = $formData['myRatingAgree'] === 'on';

        // Process form submission
        if ($request->isMethod('POST')) {
            // Validate recaptcha
            if ($helper->captchaVerify($recaptcha)) {
                return new JsonResponse([
                    'status' => false,
                    'errors' => [],
                    'message' => $translator->trans('form.messages.form_recaptcha', [], 'messages')
                ]);
            }

            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            $dateRange = explode(' | ', $formData['txtDateRange']);

            // Check errors and exist company
            if (!$validate['checkErrors'] && count($dateRange) === 2) {
                list($startDate, $endDate) = $dateRange;

                /**
                 * Get company by @slug
                 * @var Company $company
                 */
                $company = $em->getRepository(Company::class)->findOneBy(['slug' => $formData['companySlug']]);

                if (!isset($company)) {
                    return new JsonResponse([
                        'status' => false,
                        'errors' => [],
                        'message' => $translator->trans('form.messages.form_details_error', [], 'messages')
                    ]);
                }

                /**
                 * Get review by @email and @company
                 * @var CompanyReview $review
                 */
                $review = $em->getRepository(CompanyReview::class)->findOneBy([
                    'email' => $formData['email'],
                    'company' => $company
                ]);

                if (isset($review)) {
                    return new JsonResponse([
                        'status' => false,
                        'errors' => [],
                        'message' => $translator->trans('form.messages.form_review_double', [], 'messages')
                    ]);
                }

                // Send email to @company
                $subject = $translator->trans('mail.subject_new_review', [], 'messages');
                $emailSend = $mail->sendMail(
                    $company->getEmail(),
                    $subject,
                    'frontend/emails/new-review.html.twig',
                    [
                        'pageTitle' => $subject,
                        'name' => $formData['name'],
                        'surname' => $formData['surname'],
                        'review' => $formData['message']
                    ]
                );

                $validate['checkErrors'] = !$emailSend;

                // Check email send
                if ($emailSend) {
                    // Create object
                    $review = new CompanyReview();
                    $review->setUuid(Uuid::v4());
                    $review->setCompany($company);
                    $review->setUser($this->getUser());
                    $review->setName($formData['name']);
                    $review->setSurname($formData['surname']);
                    $review->setEmail($formData['email']);
                    $review->setPhone($formData['phone']);
                    $review->setDisplayName($formData['displayName']);
                    $review->setConnection($formData['houseConnections']);
                    $review->setReview($formData['message']);
                    $review->setGeneralStar($formData['generalReview']);
                    $review->setFacilityStar($formData['facilities']);
                    $review->setMaintenanceStar($formData['maintenanceSupport']);
                    $review->setCleanStar($formData['cleanliness']);
                    $review->setDignityStar($formData['dignity']);
                    $review->setBeverageStar($formData['beverages']);
                    $review->setPersonalStar($formData['personnel']);
                    $review->setActivityStar($formData['activities']);
                    $review->setSecurityStar($formData['security']);
                    $review->setManagementStar($formData['management']);
                    $review->setRoomStar($formData['rooms']);
                    $review->setPriceQualityStar($formData['priceQualityRatio']);
                    $review->setNameAgree($formData['nameAgree']);
                    $review->setRatingAgree($formData['myRatingAgree']);
                    $review->setStartDate(DateTime::createFromFormat('Y.m.d', trim($startDate)));
                    $review->setEndDate(DateTime::createFromFormat('Y.m.d', trim($endDate)));
                    $review->setTotalValuesStar(number_format($review->calculateTotalValuesStar(), 2));

                    // Parse and save
                    $em->persist($review);
                    $em->flush();
                }
            }
        }

        return new JsonResponse([
            'status' => !$validate['checkErrors'],
            'errors' => $validate['errors'],
            'message' => $translator->trans(!$validate['checkErrors'] ? 'form.messages.form_review_success' : 'form.messages.form_details_error', [], 'messages'),
        ]);
    }

    #[Route('/account/ajax/add-to-favorites', name: 'account_ajax_add_to_favorites')]
    public function addToFavorites(EntityManagerInterface $em, Request $req): JsonResponse
    {
        $id = $req->get('id');
        $type = $req->get('type');

        /**
         * @var User $user
         */
        $user = $this->getUser();

        // Check exist data
        if (empty($user) || empty($id) || empty($type) || !in_array($type, Favorite::getFavoriteTypes())) {
            return new JsonResponse([
                'status' => false
            ]);
        }

        /**
         * Get entity by @type & @id
         * @var $getEntity
         */
        $getEntity = match ($type) {
            Favorite::COURSE_FAVORITE => $em->getRepository(TrainingCourse::class)->find($id),
            Favorite::JOB_FAVORITE => $em->getRepository(Job::class)->find($id),
            Favorite::PROVIDER_FAVORITE, Favorite::CARE_FAVORITE => $em->getRepository(Company::class)->findOneBy(['id' => $id, 'locationType' => $type])
        };

        if (empty($getEntity)) {
            return new JsonResponse([
                'status' => false
            ]);
        }

        /** @var Favorite $favorite */
        $favorite = $em->getRepository(Favorite::class)->findOneBy([
            'user' => $user,
            'type' => $type,
            'entityId' => $getEntity->getId()
        ]);

        // Check status
        $emptyFavorite = empty($favorite);

        // Check exist item
        if ($emptyFavorite) {
            $favorite = new Favorite();
            $favorite->setUuid(Uuid::v4());
            $favorite->setUser($user);
            $favorite->setType($type);
            $favorite->setEntityId($id);

            // persist
            $em->persist($favorite);
        } else {
            // remove item
            $em->remove($favorite);
        }

        $em->flush();

        return new JsonResponse([
            'status' => $emptyFavorite
        ]);
    }

    #[Route('/ajax/subscribe-newsletter', name: 'ajax_subscribe_newsletter')]
    public function subscribeNewsletter(Request $request, FormValidatorHelper $validatorHelper, TranslatorInterface $translator, MailchimpAPIHelper $mailChimp): JsonResponse
    {
        // Init variables
        $validate = ['checkErrors' => false, 'errors' => []];

        // Retrieve form data from request
        $formData = $request->request->all();

        // Process form submission
        if ($request->isMethod('POST')) {
            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            // Check errors and exist company
            if (!$validate['checkErrors']) {
                try {
                    $mailChimp->addListMember($formData['emailAddress'], 'pending');
                } catch (ClientException $ex) {
                    $contents = $ex->getResponse()->getBody()->getContents();
                    $errorResponse = json_decode($contents, true);

                    if (JSON_ERROR_NONE !== json_last_error()) {
                        return new JsonResponse([
                            'status' => false,
                            'errors' => $validate['errors'],
                            'message' => $translator->trans('newsletter.errors.default')
                        ]);
                    }

                    // Check errors
                    if (isset($errorResponse['title'])) {
                        return match ($errorResponse['title']) {
                            'Member Exists' => new JsonResponse([
                                'status' => false,
                                'errors' => $validate['errors'],
                                'message' => $translator->trans('newsletter.errors.user_exists')
                            ]),
                            'Invalid Resource' => new JsonResponse([
                                'status' => false,
                                'errors' => $validate['errors'],
                                'message' => $translator->trans('newsletter.errors.email_not_valid')
                            ]),
                            default => new JsonResponse([
                                'status' => false,
                                'errors' => $validate['errors'],
                                'message' => $translator->trans('newsletter.errors.default')
                            ])
                        };
                    }

                    return new JsonResponse([
                        'status' => false,
                        'errors' => $validate['errors'],
                        'message' => $translator->trans('newsletter.errors.default')
                    ]);
                } catch (\Exception $ex) {
                    return new JsonResponse([
                        'status' => false,
                        'errors' => $validate['errors'],
                        'message' => $translator->trans('newsletter.errors.default')
                    ]);
                }
            }
        }

        return new JsonResponse([
            'status' => !$validate['checkErrors'],
            'errors' => $validate['errors'],
            'message' => $translator->trans(!$validate['checkErrors'] ? 'newsletter.errors.success' : 'newsletter.errors.default', [], 'messages'),
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/ajax/send-event-details-form', name: 'ajax_send_event_details_form')]
    public function eventDetailsForm(Request $request, FormValidatorHelper $validatorHelper, DefaultHelper $helper, TranslatorInterface $translator, MailHelper $mail): JsonResponse
    {
        // Init variables
        $validate = ['checkErrors' => false, 'errors' => []];
        $emailSend = false;

        // Retrieve form data from request
        $formData = $request->request->all();

        // Process form submission
        if ($request->isMethod('POST')) {
            // Validate recaptcha
            if ($helper->captchaVerify($request->get('g-recaptcha-response'))) {
                return new JsonResponse([
                    'status' => false,
                    'errors' => [],
                    'message' => $translator->trans('form.messages.form_recaptcha', [], 'messages')
                ]);
            }

            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            // Check errors
            if (!$validate['checkErrors']) {
                // Send email to @appEmail
                $emailSend = $mail->sendMail(
                    $this->getParameter('app_email'),
                    $translator->trans('mail.event', [], 'messages'),
                    'frontend/emails/event-details.html.twig', $formData
                );
            }
        }

        return new JsonResponse([
            'status' => $emailSend,
            'errors' => $validate['errors'],
            'message' => $translator->trans($emailSend ? 'form.messages.form_details_success' : 'form.messages.form_details_error', [], 'messages')
        ]);
    }

}