<?php

namespace App\Controller\Dashboard;

use App\Entity\Article;
use App\Entity\CategoryArticle;
use App\Entity\CategoryCare;
use App\Entity\CategoryCourse;
use App\Entity\CategoryJob;
use App\Entity\CategoryService;
use App\Entity\City;
use App\Entity\Company;
use App\Entity\CompanyGallery;
use App\Entity\Favorite;
use App\Entity\Job;
use App\Entity\Menu;
use App\Entity\TrainingCourse;
use App\Entity\User;
use App\Helper\DatatableHelper;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use App\Helper\FormValidatorHelper;
use App\Helper\LanguageHelper;
use App\Helper\MailHelper;
use App\Repository\ArticleRepository;
use App\Repository\CompanyRepository;
use App\Repository\CompanyReviewRepository;
use App\Repository\EventPartnerRepository;
use App\Repository\EventSpeakerRepository;
use App\Repository\JobRepository;
use App\Repository\LanguageRepository;
use App\Repository\MenuRepository;
use App\Repository\PageRepository;
use App\Repository\TrainingCourseRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AjaxController extends AbstractController
{
    #[Route('/dashboard/ajax/admin/pages', name: 'dashboard_ajax_pages')]
    public function getPages(Request $request, PageRepository $pageRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::PAGE_FIELDS);

        // filter by params
        $menus = $pageRepository->findPagesByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        // get total count
        $totalRecords = $pageRepository->countPages();

        // get filtered count
        $totalDisplay = count($menus);

        // pagination length
        if (isset($params['length'])) {
            $menus = array_splice($menus, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $menus
        ]);
    }

    #[Route('/dashboard/ajax/admin/languages', name: 'dashboard_ajax_languages')]
    public function getLanguages(Request $request, LanguageRepository $languageRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::LANG_FIELDS);

        // filter by params
        $languages = $languageRepository->findLanguagesByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        // get total count
        $totalRecords = $languageRepository->countLanguages();

        // get filtered count
        $totalDisplay = count($languages);

        // pagination length
        if (isset($params['length'])) {
            $languages = array_splice($languages, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $languages
        ]);
    }

    #[Route('/dashboard/ajax/admin/users', name: 'dashboard_ajax_users')]
    public function getUsers(Request $request, UserRepository $userRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::USER_FIELDS);

        // filter by params
        $users = $userRepository->findUsersByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        // get total count
        $totalRecords = $userRepository->countUsers(User::ROLE_CLIENT);

        // get filtered count
        $totalDisplay = count($users);

        // pagination length
        if (isset($params['length'])) {
            $users = array_splice($users, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $users
        ]);
    }

    #[Route('/dashboard/ajax/companies/{locationType}', name: 'dashboard_ajax_companies')]
    public function getCompanies(Request $request, CompanyRepository $companyRepository, DatatableHelper $datatableHelper, $locationType): JsonResponse
    {
        // Get all params from the request
        $params = $request->query->all();

        /** @var User $user */
        $user = $this->isGranted('ROLE_ADMIN') ? null : $this->getUser();

        // Get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::COMPANY_FIELDS);

        // Filter by params
        $companies = $companyRepository->findCompaniesByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword'],
            $locationType,
            $user
        );

        // Get total count
        $totalRecords = $companyRepository->countCompanies($user, $locationType);


        // Get filtered count
        $totalDisplay = count($companies);

        // Pagination length
        if (isset($params['length'])) {
            $companies = array_splice($companies, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $companies
        ]);
    }

    #[Route('/dashboard/ajax/company/{uuid}/upload-gallery', name: 'dashboard_ajax_company_upload_gallery')]
    public function companyUploadGallery(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, $uuid, TranslatorInterface $translator): Response
    {
        /**
         * Get company by @uuid
         * @var Company $company
         */
        $company = $em->getRepository(Company::class)->findOneBy(['uuid' => $uuid]);

        if (null === $company) {
            return new JsonResponse([
                'success' => false,
                'message' => $translator->trans('controller.no_account', [], 'messages')
            ]);
        }

        // Get file
        $file = $request->files->get('file');

        // Upload file
        $uploadFile = $fileUploader->uploadFile(
            $file,
            null,
            $this->getParameter('app_company_gallery_path')
        );

        // Check and set @filename
        if (!$uploadFile['success']) {
            return new JsonResponse([
                'success' => false,
                'message' => $translator->trans('controller.error_upload_file', [], 'messages')
            ]);
        }

        $gallery = new CompanyGallery();
        $gallery->setFileName($uploadFile['fileName']);
        $gallery->setCompany($company);

        $em->persist($gallery);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'id' => $gallery->getId(),
            'fileName' => $uploadFile['fileName'],
            'message' => $translator->trans('controller.success_gallery_images_upload', [], 'messages')
        ]);
    }

    #[Route('/dashboard/ajax/company/{uuid}/remove-item-gallery', name: 'dashboard_ajax_company_remove_gallery')]
    public function companyRemoveItemGallery(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, $uuid, TranslatorInterface $translator): Response
    {
        $id = $request->get('id');

        /**
         * Get company by @uuid
         * @var Company $company
         */
        $company = $em->getRepository(Company::class)->findOneBy(['uuid' => $uuid]);

        if (null === $company && !isset($id)) {
            return new JsonResponse([
                'success' => false,
                'message' => $translator->trans('controller.no_account', [], 'messages')
            ]);
        }

        /**
         * Get gallery by @id
         * @var CompanyGallery $companyGallery
         */
        $companyGallery = $em->getRepository(CompanyGallery::class)->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        if (null === $companyGallery) {
            return new JsonResponse([
                'success' => false,
                'message' => $translator->trans('controller.no_gallery_file', [], 'messages')
            ]);
        }

        try {
            //Remove file for the filesystem
            $result = $fileUploader->removeFile($this->getParameter('app_company_gallery_path'), $companyGallery->getFileName());

            // Check result and remove item
            if ($result) {
                // Remove file DB
                $em->remove($companyGallery);
                $em->flush();
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return new JsonResponse([
            'success' => $result,
            'message' => $translator->trans('controller.success_file_deleted', [], 'messages') . $companyGallery->getFileName()
        ]);
    }

    #[Route('/dashboard/ajax/admin/reviews', name: 'dashboard_ajax_reviews')]
    public function getCompanyReviews(Request $request, CompanyReviewRepository $companyReviewRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::REVIEW_FIELDS);

        // filter by params
        $reviews = $companyReviewRepository->findReviewsByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        // get total count
        $totalRecords = $companyReviewRepository->countReviews();

        // get filtered count
        $totalDisplay = count($reviews);

        // pagination length
        if (isset($params['length'])) {
            $reviews = array_splice($reviews, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $reviews
        ]);
    }

    #[Route('/dashboard/ajax/articles', name: 'dashboard_ajax_articles')]
    public function getArticles(Request $request, ArticleRepository $articleRepository, DatatableHelper $datatableHelper, LanguageHelper $languageHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        /** @var User $user */
        $user = $this->isGranted('ROLE_ADMIN') ? null : $this->getUser();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::ARTICLE_FIELDS);

        // get default language
        $defaultLanguage = $languageHelper->getDefaultLanguage();

        // filter by params
        $articles = $articleRepository->findArticlesByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword'],
            $defaultLanguage,
            $user
        );

        // get total count
        $totalRecords = $articleRepository->countArticles($user);

        // get filtered count
        $totalDisplay = count($articles);

        // pagination length
        if (isset($params['length'])) {
            $articles = array_splice($articles, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $articles
        ]);
    }

    #[Route('/dashboard/ajax/admin/speakers', name: 'dashboard_ajax_speakers')]
    public function getSpeakers(Request $request, EventSpeakerRepository $eventSpeakerRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::SPEAKER_FIELDS);

        // filter by params
        $speakers = $eventSpeakerRepository->findSpeakersByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        // get total count
        $totalRecords = $eventSpeakerRepository->countSpeakers();

        // get filtered count
        $totalDisplay = count($speakers);

        // pagination length
        if (isset($params['length'])) {
            $speakers = array_splice($speakers, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $speakers
        ]);
    }

    #[Route('/dashboard/ajax/admin/partners', name: 'dashboard_ajax_partners')]
    public function getPartners(Request $request, EventPartnerRepository $eventPartnerRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::PARTNER_FIELDS);

        // filter by params
        $partners = $eventPartnerRepository->findPartnersByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        // get total count
        $totalRecords = $eventPartnerRepository->countPartners();

        // get filtered count
        $totalDisplay = count($partners);

        // pagination length
        if (isset($params['length'])) {
            $partners = array_splice($partners, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $partners
        ]);
    }

    #[Route('/dashboard/ajax/jobs', name: 'dashboard_ajax_jobs')]
    public function getJobs(Request $request, JobRepository $jobRepository, DatatableHelper $datatableHelper, LanguageHelper $languageHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::JOB_FIELDS);

        /** @var User $user */
        $user = $this->isGranted('ROLE_ADMIN') ? null : $this->getUser();

        // get default language
        $defaultLanguage = $languageHelper->getDefaultLanguage();

        // filter by params
        $articles = $jobRepository->findJobsByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword'],
            $defaultLanguage,
            $user
        );

        // get total count
        $totalRecords = $jobRepository->countJobs($user);

        // get filtered count
        $totalDisplay = count($articles);

        // pagination length
        if (isset($params['length'])) {
            $articles = array_splice($articles, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $articles
        ]);
    }

    #[Route('/dashboard/ajax/training-course', name: 'dashboard_ajax_training_course')]
    public function getTrainingCourses(Request $request, TrainingCourseRepository $courseRepository, DatatableHelper $datatableHelper, LanguageHelper $languageHelper): JsonResponse
    {
        // Get all params from the request
        $params = $request->query->all();

        // Get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::TRAINING_FIELDS);

        // Get default language
        $defaultLanguage = $languageHelper->getDefaultLanguage();

        /** @var User $user */
        $user = $this->isGranted('ROLE_ADMIN') ? null : $this->getUser();

        // Filter by params
        $trainingCourses = $courseRepository->findTrainingCourseByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword'],
            $defaultLanguage,
            $user
        );

        // Get total count
        $totalRecords = $courseRepository->countTrainingCourse($user);

        // Get filtered count
        $totalDisplay = count($trainingCourses);

        // Pagination length
        if (isset($params['length'])) {
            $trainingCourses = array_splice($trainingCourses, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $trainingCourses
        ]);
    }

    #[Route('/dashboard/ajax/admin/default-categories/{type}', name: 'dashboard_ajax_default_categories')]
    public function getDefaultCategories(Request $request, EntityManagerInterface $em, DatatableHelper $datatableHelper, LanguageHelper $languageHelper, $type): JsonResponse
    {
        $categoryClass = null;
        $categoryTranslation = '';

        // Check exist type
        if (empty($type) || !in_array($type, DefaultHelper::CATEGORY_TYPES)) {
            return new JsonResponse([
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::DEFAULT_CATEGORY_FIELDS);

        // get default language
        $defaultLanguage = $languageHelper->getDefaultLanguage();

        // Check type and set entity
        switch ($type) {
            case 'training':
                $categoryClass = CategoryCourse::class;
                $categoryTranslation = 'categoryCourseTranslations';
                break;
            case 'article':
                $categoryClass = CategoryArticle::class;
                $categoryTranslation = 'categoryArticleTranslations';
                break;
            case 'job':
                $categoryClass = CategoryJob::class;
                $categoryTranslation = 'categoryJobTranslations';
                break;
            case Company::LOCATION_TYPE_CARE:
                $categoryClass = CategoryCare::class;
                $categoryTranslation = 'categoryCareTranslations';
                break;
            case Company::LOCATION_TYPE_PROVIDER:
                $categoryClass = CategoryService::class;
                $categoryTranslation = 'categoryServiceTranslations';
                break;
        }

        $categoryRepository = $em->getRepository($categoryClass);

        // Filter by params
        $categories = $categoryRepository->findCategoriesByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword'],
            $defaultLanguage,
            $categoryTranslation
        );

        // get total count
        $totalRecords = $categoryRepository->countCategories();

        // get filtered count
        $totalDisplay = count($categories);

        // pagination length
        if (isset($params['length'])) {
            $categories = array_splice($categories, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $categories
        ]);
    }

    #[Route('/dashboard/ajax/admin/menus', name: 'dashboard_ajax_menus')]
    public function getMenus(Request $request, MenuRepository $menuRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::MENU_FIELDS);

        // filter by params
        $menus = $menuRepository->findMenusByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        // get total count
        $totalRecords = $menuRepository->countMenus();

        // get filtered count
        $totalDisplay = count($menus);

        // pagination length
        if (isset($params['length'])) {
            $menus = array_splice($menus, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $menus
        ]);
    }

    #[Route('/dashboard/ajax/admin/menu-items/{uuid}', name: 'dashboard_ajax_menu_items')]
    public function getMenuItems(EntityManagerInterface $em, LanguageHelper $languageHelper, Request $request, $uuid): JsonResponse
    {
        $locale = $request->get('locale', $this->getParameter('default_locale'));

        /**
         * Get menu by @uuid
         * @var Menu $menu
         */
        $menu = $em->getRepository(Menu::class)->findOneBy(['uuid' => $uuid]);

        if (null === $menu) {
            return new JsonResponse([
                'data' => []
            ]);
        }

        $menuItems = $em->getRepository(Menu::class)->getMenuItemsByMenu(
            $languageHelper->getLanguageByLocale($locale),
            $menu
        );

        return new JsonResponse([
            'data' => $menuItems
        ]);
    }

    #[Route('/ajax/county/cities', name: 'dashboard_ajax_cities')]
    public function getCitiesByCounty(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $id = $request->get('id');

        if (!isset($id)) {
            return new JsonResponse([
                'status' => false,
                'cities' => []
            ]);
        }

        /** Get city by @id */
        $cities = $em->getRepository(City::class)->findCitiesByCounty($id);

        return new JsonResponse([
            'status' => true,
            'cities' => $cities
        ]);
    }

    #[Route('/dashboard/ajax/profile/get-favorites', name: 'dashboard_ajax_favorites')]
    public function getFavorites(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $page = $request->get('page', 1);
        $sortName = $request->get('sortName', 'createdAt');
        $sortOrder = $request->get('sortOrder', 'DESC');
        $type = $request->get('type', '');

        /** @var User $user */
        $user = $this->getUser();

        // Calculate offset
        $offset = ($page - 1) * $limit;

        // Get data favorites by @filters
        $favorites = $em->getRepository(Favorite::class)->getFavoritesByFilters($user, $type, $sortName, $sortOrder, $limit, $offset);

        // Get total favorites by @filters
        $countFavorites = $em->getRepository(Favorite::class)->getFavoritesByFilters($user, $type, $sortName, $sortOrder, $limit, $offset, true);

        // Calculate totalPage / limit
        $totalPages = ceil($countFavorites / $limit);

        return new JsonResponse([
            'status' => true,
            'rows' => $favorites,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    #[Route('/dashboard/ajax/profile/remove-favorites', name: 'dashboard_ajax_remove_favorite')]
    public function removeFavorites(EntityManagerInterface $em, Request $request, TranslatorInterface $translator): JsonResponse
    {
        $uuid = $request->get('uuid');

        /** @var User $user */
        $user = $this->getUser();

        if (empty($uuid)) {
            return new JsonResponse([
                'status' => false,
                'message' => $translator->trans('form.default.required', [], 'messages')
            ]);
        }

        /**
         * Check favorite by @user and @uuid
         * @var Favorite $favorite
         */
        $favorite = $em->getRepository(Favorite::class)->findOneBy(['user' => $user, 'uuid' => $uuid]);

        // Check exist favorite
        if (empty($favorite)) {
            return new JsonResponse([
                'status' => false,
                'message' => $translator->trans('form.default.required', [], 'messages')
            ]);
        }

        // remove item
        $em->remove($favorite);
        $em->flush();

        return new JsonResponse([
            'status' => true
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/dashboard/ajax/profile/change-email', name: 'dashboard_ajax_change_email')]
    public function changeEmail(EntityManagerInterface $em, Request $request, FormValidatorHelper $validatorHelper, TranslatorInterface $translator, MailHelper $mail, Security $security): JsonResponse
    {
        // Init variables
        $emailSend = false;
        $validate = ['checkErrors' => false, 'errors' => []];

        // Retrieve form data from request
        $email = $request->get('emailAddress');

        // Process form submission
        if ($request->isMethod('POST')) {
            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate(['emailAddress' => $email]);

            // Check errors and exist company
            if (!$validate['checkErrors']) {
                $hash = DefaultHelper::generateHash($email);

                /** @var User $getUser */
                $getUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);

                if (empty($getUser)) {
                    /** @var User $user */
                    $user = $this->getUser();

                    $user->setEmail($email);
                    $user->setEnabled(false);
                    $user->setConfirmationToken($hash);

                    // Persiste email
                    $em->persist($user);

                    // Send email to @user
                    $subject = $translator->trans('auth.register_subject_mail', [], 'messages') . ' - ' . $this->getParameter('app_name');
                    $emailSend = $mail->sendMail(
                        $email,
                        $subject,
                        'frontend/emails/auth/change-email.html.twig',
                        [
                            'pageTitle' => $subject,
                            'url' => $this->generateUrl('app_register_confirm', [
                                'token' => $hash
                            ], UrlGeneratorInterface::ABSOLUTE_URL)
                        ]
                    );

                    // Check send email and create user
                    if ($emailSend) {
                        $em->flush();
                    }
                }
            }
        }

        return new JsonResponse([
            'status' => $emailSend,
            'errors' => $validate['errors'],
            'message' => $translator->trans($emailSend ? 'form.messages.success_edit_profile' : 'form.messages.form_details_error', [], 'messages')
        ]);
    }

    #[Route('/dashboard/ajax/profile/change-user-data/{type}', name: 'dashboard_ajax_change_user_data')]
    public function changeUserData(EntityManagerInterface $em, FileUploader $uploader, Request $request, FormValidatorHelper $validatorHelper, TranslatorInterface $translator, UserPasswordHasherInterface $passwordEncoder, $type): JsonResponse
    {
        // Init variables
        $validate = ['checkErrors' => false, 'errors' => []];
        $uploadFile = false;

        // Retrieve form data from request
        $formData = $request->request->all();
        $file = $request->files->get('fileName');
        if (isset($file)) $formData['fileName'] = $file;

        // Process form submission
        if ($request->isMethod('POST')) {
            /**
             * Validate fields by @formData
             * @var FormValidatorHelper $validator
             */
            $validate = $validatorHelper->validate($formData);

            // Check errors
            if (!$validate['checkErrors']) {
                // Check and upload file
                if (isset($file)) {
                    $uploadFile = $uploader->uploadFile(
                        $file,
                        null,
                        $this->getParameter('app_user_path')
                    );
                }

                /** @var User $user */
                $user = $this->getUser();

                // Check type
                switch ($type) {
                    case 'name':
                        $user->setName($formData['name']);
                        break;
                    case 'surname':
                        $user->setSurname($formData['surname']);
                        break;
                    case 'phone':
                        $user->setPhone($formData['phone']);
                        break;
                    case 'fileName':
                        $uploadFile['success'] ?
                            $user->setProfilePicture($uploadFile['fileName']) :
                            $validate['checkErrors'] = true;
                        break;
                    case 'password':
                        $user->setPassword($passwordEncoder->hashPassword($user, $formData['password']));
                        $user->setPasswordChangedAt(new \DateTime());
                        break;
                    default:
                        $validate['checkErrors'] = true;
                        break;
                }

                $em->persist($user);
                $em->flush();
            }
        }

        $successMessage = $type === 'password' ? 'auth.reset_success' : 'form.messages.success_edit';
        $fieldValue = $type === 'fileName' ? $uploadFile['fileName'] : $formData[$type];

        return new JsonResponse([
            'status' => !$validate['checkErrors'],
            'type' => $type,
            'errors' => $validate['errors'],
            'message' => $translator->trans(!$validate['checkErrors'] ? $successMessage : 'form.default.required', [], 'messages'),
            $type => $fieldValue
        ]);
    }

    #[Route('/dashboard/ajax/profile/delete-account', name: 'dashboard_ajax_delete_account')]
    public function deleteAccount(EntityManagerInterface $em, Request $request, FormValidatorHelper $validatorHelper, TranslatorInterface $translator): JsonResponse
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

            // Check errors
            if (!$validate['checkErrors'] && !$this->isGranted('ROLE_ADMIN')) {
                /** @var User $user */
                $user = $this->getUser();

                // User dates
                $articles = $user->getArticles();
                $courses = $user->getTrainingCourses();
                $jobs = $user->getJobs();
                $companies = $user->getCompanies();

                /**
                 * Parse and delete articles
                 * @var Article $article
                 */
                foreach ($articles as $article) {
                    $article->setDeletedAt(new \DateTime());
                    $article->setStatus(Article::STATUS_DRAFT);

                    $em->persist($article);
                    $em->flush();
                }

                /**
                 * Parse and delete courses
                 * @var TrainingCourse $course
                 */
                foreach ($courses as $course) {
                    $course->setDeletedAt(new \DateTime());
                    $course->setStatus(TrainingCourse::STATUS_DRAFT);

                    $em->persist($course);
                    $em->flush();
                }

                /**
                 * Parse and delete jobs
                 * @var Job $job
                 */
                foreach ($jobs as $job) {
                    $job->setDeletedAt(new \DateTime());
                    $job->setStatus(Job::STATUS_DRAFT);

                    $em->persist($job);
                    $em->flush();
                }

                /**
                 * Parse and delete companies
                 * @var Company $company
                 */
                foreach ($companies as $company) {
                    $company->setDeletedAt(new \DateTime());
                    $company->setStatus(Company::STATUS_DRAFT);

                    $em->persist($company);
                    $em->flush();
                }

                $user->setReasonForDeletion($formData['option'] ?? $formData['shortMessage']);
                $user->setDeletedAt(new \DateTime());
                $user->setEnabled(false);
                $em->persist($user);
                $em->flush();
            }
        }

        return new JsonResponse([
            'status' => !$validate['checkErrors'],
            'errors' => $validate['errors'],
            'message' => $translator->trans(!$validate['checkErrors'] ? 'form.messages.success_deleted' : 'form.default.required', [], 'messages')
        ]);
    }

    #[Route('/dashboard/ajax/chart/get-users', name: 'dashboard_ajax_get_users')]
    public function getUsersByYear(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $year = $request->get('year', (new DateTime())->format('Y'));

        // Get data by @year and @role
        $clientData = $em->getRepository(User::class)->getUsersByMonthAndYear($year, User::ROLE_CLIENT);
        $companyData = $em->getRepository(User::class)->getUsersByMonthAndYear($year, User::ROLE_COMPANY);

        // Parse and mapped data
        $dataUser = DefaultHelper::mappedDataForMonths($clientData, $year);
        $dataCompany = DefaultHelper::mappedDataForMonths($companyData, $year);

        return new JsonResponse([
            'clients' => [
                'labels' => $dataUser['labels'],
                'values' => $dataUser['values']
            ],
            'companies' => [
                'labels' => $dataCompany['labels'],
                'values' => $dataCompany['values']
            ]
        ]);
    }

    #[Route('/dashboard/ajax/chart/get-user-data', name: 'dashboard_ajax_get_user_data')]
    public function getUserData(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $year = $request->get('year', (new DateTime())->format('Y'));
        $userId = $request->get('userId', 0);

        /** @var User $getUser */
        $getUser = $em->getRepository(User::class)->find($userId);

        // Get data by @year and @user && Parse and mapped data
        $articleData = $em->getRepository(Article::class)->getDataByMonthAndYear($year, $getUser ?? null);
        $dataArticle = DefaultHelper::mappedDataForMonths($articleData, $year);


        // Get data by @year and @user && Parse and mapped data
        $jobData = $em->getRepository(Job::class)->getDataByMonthAndYear($year, $getUser ?? null);
        $dataJob = DefaultHelper::mappedDataForMonths($jobData, $year);

        // Get data by @year and @user && Parse and mapped data
        $courseData = $em->getRepository(TrainingCourse::class)->getDataByMonthAndYear($year, $getUser ?? null);
        $dataCourse = DefaultHelper::mappedDataForMonths($courseData, $year);

        // Get data by @year and @user && Parse and mapped data
        $careData = $em->getRepository(Company::class)->getDataByMonthAndYear($year, $getUser ?? null);
        $dataCare = DefaultHelper::mappedDataForMonths($careData, $year);

        // Get data by @year and @user && Parse and mapped data
        $providerData = $em->getRepository(Company::class)->getDataByMonthAndYear($year, $getUser ?? null, Company::LOCATION_TYPE_PROVIDER);
        $dataProvider = DefaultHelper::mappedDataForMonths($providerData, $year);

        return new JsonResponse([
            'articles' => [
                'labels' => $dataArticle['labels'],
                'values' => $dataArticle['values']
            ],
            'jobs' => [
                'labels' => $dataJob['labels'],
                'values' => $dataJob['values']
            ],
            'courses' => [
                'labels' => $dataCourse['labels'],
                'values' => $dataCourse['values']
            ],
            'cares' => [
                'labels' => $dataCare['labels'],
                'values' => $dataCare['values']
            ],
            'providers' => [
                'labels' => $dataProvider['labels'],
                'values' => $dataProvider['values']
            ]
        ]);
    }
}
