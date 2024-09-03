<?php

namespace App\Controller\Dashboard;

use App\Entity\City;
use App\Entity\Education;
use App\Entity\EducationRegistration;
use App\Entity\EducationSchedule;
use App\Entity\Menu;
use App\Entity\TeamMember;
use App\Entity\User;
use App\Helper\DatatableHelper;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use App\Repository\ArticleRepository;
use App\Repository\CertificationRepository;
use App\Repository\EducationRepository;
use App\Repository\EducationRegistrationRepository;
use App\Repository\FeedbackRepository;
use App\Repository\GalleryRepository;
use App\Repository\LanguageRepository;
use App\Repository\LeadRepository;
use App\Repository\MenuRepository;
use App\Repository\PageRepository;
use App\Repository\TeamMemberRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use DateTime;

class AjaxController extends AbstractController
{
    #[Route('/dashboard/ajax/users', name: 'dashboard_ajax_users')]
    public function getUsers(Request $request, UserRepository $userRepository, DatatableHelper $datatableHelper, EntityManagerInterface $em): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();
        $startDate = null;
        $endDate = null;

        if (isset($params['range']) && !empty($params['range'])) {
            list($startDate, $endDate) = explode(' | ', $params['range']);

            $startDate = DateTime::createFromFormat('d-m-Y', $startDate);
            $endDate = DateTime::createFromFormat('d-m-Y', $endDate);
        }

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams($params, $datatableHelper::USER_FIELDS);

        // filter by params
        $users = $userRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword'],
            $params['role']
        );

        foreach ($users as $key => $user) {
            $educationRegistrationsCount = $em->getRepository(EducationRegistration::class)->getEducationRegistrationCount($user['id'], $startDate, $endDate);
            $users[$key]['educationRegistrationsCount'] = $educationRegistrationsCount;
        }

        // get total count
        $totalRecords = $userRepository->findTotalCount(User::ROLE_CLIENT);

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

    #[Route('/dashboard/ajax/education/{uuid}/registrations', name: 'dashboard_ajax_education_registrations')]
    public function getEducationRegistrations(Request $request, EducationRegistrationRepository $redistrationsRepository, DatatableHelper $datatableHelper, $uuid): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams($params, $datatableHelper::EDUCATION_REPOSITORY_FIELDS);

        // filter by params        
        $registrations = $redistrationsRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword'],
            $uuid
        );

        return $this->getFilteredData($redistrationsRepository, $registrations, $params);
    }

    #[Route('/dashboard/ajax/educations/{type}', name: 'dashboard_ajax_educations')]
    public function getEducations(Request $request, EducationRepository $educationRepository, DatatableHelper $datatableHelper, LanguageHelper $languageHelper, $type): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams($params, $datatableHelper::EDUCATION_FIELDS);

        // get default language
        $defaultLanguage = $languageHelper->getDefaultLanguage();

        // filter by params
        $educations = $educationRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword'],
            $type,
            $defaultLanguage
        );

        // get total count
        $totalRecords = $educationRepository->findTotalCount($type);

        // get filtered count
        $totalDisplay = count($educations);

        // pagination length
        if (isset($params['length'])) {
            $educations = array_splice($educations, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $educations
        ]);
    }

    #[Route('/dashboard/ajax/articles', name: 'dashboard_ajax_articles')]
    public function getArticles(Request $request, ArticleRepository $articleRepository, DatatableHelper $datatableHelper, LanguageHelper $languageHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams($params, $datatableHelper::ARTICLE_FIELDS);

        // get default language
        $defaultLanguage = $languageHelper->getDefaultLanguage();

        // filter by params
        $articles = $articleRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword'],
            $defaultLanguage
        );

        return $this->getFilteredData($articleRepository, $articles, $params);
    }

    #[Route('/dashboard/ajax/leads', name: 'dashboard_ajax_leads')]
    public function getLeads(Request $request, LeadRepository $leadRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams($params, $datatableHelper::LEAD_FIELDS);

        // filter by params
        $leads = $leadRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        return $this->getFilteredData($leadRepository, $leads, $params);
    }

    #[Route('/dashboard/ajax/team-members', name: 'dashboard_ajax_team_members')]
    public function getTeamMembers(Request $request, TeamMemberRepository $teamMemberRepository, DatatableHelper $datatableHelper, EntityManagerInterface $em): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();
        $startDate = null;
        $endDate = null;

        if (isset($params['range']) && !empty($params['range'])) {
            list($startDate, $endDate) = explode(' | ', $params['range']);

            $startDate = DateTime::createFromFormat('d-m-Y', $startDate);
            $endDate = DateTime::createFromFormat('d-m-Y', $endDate);
        }

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams($params, $datatableHelper::TEAM_MEMBER_FIELDS);

        // filter by params
        $teamMembers = $teamMemberRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        foreach ($teamMembers as $key => $teamMember) {
            $tm = $em->getRepository(TeamMember::class)->find($teamMember['id']);

            if (null !== $tm) {
                $educationsCount = $tm->getEducationsFilteredCount($startDate, $endDate);
                $teamMembers[$key]['teamMemberEducations'] = $educationsCount;
            }
        }

        return $this->getFilteredData($teamMemberRepository, $teamMembers, $params);
    }

    #[Route('/dashboard/ajax/galleries', name: 'dashboard_ajax_galleries')]
    public function getGalleries(Request $request, GalleryRepository $galleryRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams($params, $datatableHelper::GALLERY_FIELDS);

        // filter by params
        $galleries = $galleryRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        return $this->getFilteredData($galleryRepository, $galleries, $params);
    }

    #[Route('/dashboard/ajax/languages', name: 'dashboard_ajax_languages')]
    public function getLanguages(Request $request, LanguageRepository $languageRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams($params, $datatableHelper::LANGUAGE_FIELDS);

        // filter by params
        $languages = $languageRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        return $this->getFilteredData($languageRepository, $languages, $params);
    }

    #[Route('/dashboard/ajax/menus', name: 'dashboard_ajax_menus')]
    public function getMenus(Request $request, MenuRepository $menuRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams($params, $datatableHelper::MENU_FIELDS);

        // filter by params
        $menus = $menuRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        // get total count
        return $this->getFilteredData($menuRepository, $menus, $params);
    }

    #[Route('/dashboard/ajax/menu-items/{uuid}', name: 'dashboard_ajax_menu_items')]
    public function getMenuItems(EntityManagerInterface $em, LanguageHelper $languageHelper, Request $request, $uuid): JsonResponse
    {
        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $language = $languageHelper->getLanguageByLocale($locale);

        $menu = $em->getRepository(Menu::class)->findOneBy(['uuid' => $uuid]);

        if (null === $menu) {
            return new JsonResponse([
                'data' => []
            ]);
        }

        $menuItems = $em->getRepository(Menu::class)->getMenuItemsByMenu(
            $language,
            $menu
        );

        return new JsonResponse([
            'data' => $menuItems
        ]);
    }

    #[Route('/dashboard/ajax/pages', name: 'dashboard_ajax_pages')]
    public function getPages(Request $request, PageRepository $pageRepository, DatatableHelper $datatableHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams((array)$params, $datatableHelper::PAGE_FIELDS);

        // filter by params
        $pages = $pageRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword']
        );

        return $this->getFilteredData($pageRepository, $pages, $params);
    }

    #[Route('/dashboard/ajax/feedback', name: 'dashboard_ajax_feedback')]
    public function getFeedback(Request $request, FeedbackRepository $feedbackRepository, DatatableHelper $datatableHelper, LanguageHelper $languageHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams($params, $datatableHelper::FEEDBACK_FIELDS);

        // get default language
        $defaultLanguage = $languageHelper->getDefaultLanguage();

        // filter by params
        $languages = $feedbackRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword'],
            $defaultLanguage
        );

        return $this->getFilteredData($feedbackRepository, $languages, $params);
    }

    #[Route('/dashboard/ajax/certifications', name: 'dashboard_ajax_certifications')]
    public function getCertifications(Request $request, CertificationRepository $certificationRepository, DatatableHelper $datatableHelper, LanguageHelper $languageHelper): JsonResponse
    {
        // get all params from the request
        $params = $request->query->all();

        // get sortable fields
        $tableParams = $datatableHelper->getTableParams($params, $datatableHelper::CERTIFICATION_FIELDS);

        // get default language
        $defaultLanguage = $languageHelper->getDefaultLanguage();

        // filter by params
        $menus = $certificationRepository->findByFilters(
            $tableParams['column'],
            $tableParams['dir'],
            $tableParams['keyword'],
            $defaultLanguage
        );

        // get total count
        return $this->getFilteredData($certificationRepository, $menus, $params);
    }

    #[Route('/dashboard/ajax/clear-cache', name: 'dashboard_ajax_clear_cache')]
    public function cacheClear(CacheInterface $cache): JsonResponse
    {
        try {
            $cache = $cache->clear();

            if ($cache) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'You have successfully cleared the cache'
                ]);
            }
        } catch (\Exception $exception) {
            return new JsonResponse([
                'success' => false,
                'message' => 'An error occurred. Please try again later'
            ]);
        }
    }

    public function getFilteredData($entityRepository, array $entity, array $params): JsonResponse
    {
        $totalRecords = $entityRepository->findTotalCount();

        // get filtered count
        $totalDisplay = count($entity);

        // pagination length
        if (isset($params['length'])) {
            $entity = array_splice($entity, $params['start'], $params['length'] === '-1' ? $totalRecords : $params['length']);
        }

        return new JsonResponse([
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplay,
            'data' => $entity
        ]);
    }

    #[Route('/dashboard/ajax/county/cities', name: 'dashboard_ajax_cities')]
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

    #[Route('/dashboard/ajax/education/schedule/{id}/delete', name: 'dashboard_ajax_education_schedule_delete')]
    public function deleteSchedule(EntityManagerInterface $em, $id): Response
    {
        $educationSchedule = $em->getRepository(EducationSchedule::class)->findOneBy(['id' => $id]);

        if (null === $educationSchedule) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Schedule not found.'
            ]);
        }

        $em->remove($educationSchedule);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'You have successfully deleted the schedule.'
        ]);
    }

    #[Route('/dashboard/ajax/upload-image', name: 'dashboard_ajax_upload_image')]
    public function uploadImage(Request $request, FileUploader $fileUploader): Response
    {
        $file = $request->files->get('file');

        if (!$file) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No file uploaded.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $uploadDir = $this->getParameter('app_page_path');

        $uploadFile = $fileUploader->uploadFile(
            $file,
            [],
            $uploadDir . 'pagewidget/'
        );

        if ($uploadFile['success']) {
            return new JsonResponse([
                'success' => true,
                'path' => sprintf("%s%s", $request->getSchemeAndHttpHost(), $uploadDir . 'pagewidget/' . $uploadFile['fileName']),
                'message' => 'The file has been uploaded successfully.'
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'An error occurred on uploading the file.'
        ]);
    }

    #[Route('/dashboard/ajax/chart/get-education-data', name: 'dashboard_ajax_get_education_data')]
    public function getEducationData(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $year = $request->get('year', (new DateTime())->format('Y'));

        $coursesData = $em->getRepository(Education::class)->getDataByMonthAndYear($year, Education::TYPE_COURSE);
        $dataCourses = DefaultHelper::mappedDataForMonths($coursesData, $year);

        $workshopData = $em->getRepository(Education::class)->getDataByMonthAndYear($year, Education::TYPE_WORKSHOP);
        $dataWorkshop = DefaultHelper::mappedDataForMonths($workshopData, $year);

        return new JsonResponse([
            'courses' => [
                'labels' => $dataCourses['labels'],
                'values' => $dataCourses['values']
            ],
            'workshops' => [
                'labels' => $dataWorkshop['labels'],
                'values' => $dataWorkshop['values']
            ],
        ]);
    }

    #[Route('/dashboard/ajax/chart/get-participants-data', name: 'dashboard_ajax_get_participants_data')]
    public function getParticipantsData(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $year = $request->get('year', (new DateTime())->format('Y'));

        $coursesData = $em->getRepository(EducationRegistration::class)->getUserDataByMonthAndYear($year, Education::TYPE_COURSE, EducationRegistration::PAYMENT_STATUS_SUCCESS);
        $dataCourses = DefaultHelper::mappedDataForMonths($coursesData, $year);

        $workshopData = $em->getRepository(EducationRegistration::class)->getUserDataByMonthAndYear($year, Education::TYPE_WORKSHOP, EducationRegistration::PAYMENT_STATUS_SUCCESS);
        $dataWorkshop = DefaultHelper::mappedDataForMonths($workshopData, $year);

        $conventionsData = $em->getRepository(EducationRegistration::class)->getUserDataByMonthAndYear($year, Education::TYPE_CONVENTION, EducationRegistration::PAYMENT_STATUS_SUCCESS);
        $dataConventions = DefaultHelper::mappedDataForMonths($conventionsData, $year);

        return new JsonResponse([
            'courses' => [
                'labels' => $dataCourses['labels'],
                'values' => $dataCourses['values']
            ],
            'workshops' => [
                'labels' => $dataWorkshop['labels'],
                'values' => $dataWorkshop['values']
            ],
            'conventions' => [
                'labels' => $dataConventions['labels'],
                'values' => $dataConventions['values']
            ],
        ]);
    }

    #[Route('/dashboard/ajax/chart/get-sum-data', name: 'dashboard_ajax_get_sum_data')]
    public function getIncasariData(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $year = $request->get('year', (new DateTime())->format('Y'));
        $educationId = $request->get('educationId', 'all');
        $educationType = $request->get('type', 'all');

        $coursesData = $em->getRepository(EducationRegistration::class)->getDataByEducation($educationId, $educationType);
        $dataCourses = DefaultHelper::mappedDataForMonths($coursesData, $year);

        $workshopData = $em->getRepository(EducationRegistration::class)->getDataByEducation($educationId, $educationType);
        $dataWorkshop = DefaultHelper::mappedDataForMonths($workshopData, $year);

        return new JsonResponse([
            'courses' => [
                'labels' => $dataCourses['labels'] ?? [],
                'values' => $dataCourses['values'] ?? []
            ],
            'workshops' => [
                'labels' => $dataWorkshop['labels'] ?? [],
                'values' => $dataWorkshop['values'] ?? []
            ],
        ]);
    }
}
