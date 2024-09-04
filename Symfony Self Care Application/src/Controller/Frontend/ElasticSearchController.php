<?php

namespace App\Controller\Frontend;

use App\Entity\Company;
use App\Helper\DefaultHelper;
use App\Helper\ElasticSearchHelper;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Elastica\Index;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class ElasticSearchController extends AbstractController
{
    /**
     * @var Index
     */
    private Index $articleFinder;

    /**
     * @var Index
     */
    private Index $courseFinder;

    /**
     * @var Index
     */
    private Index $jobFinder;

    /**
     * @var Index
     */
    private Index $companyFinder;

    /**
     * @param Index $articleFinder
     * @param Index $courseFinder
     * @param Index $jobFinder
     * @param Index $companyFinder
     */
    public function __construct(Index $articleFinder, Index $courseFinder, Index $jobFinder, Index $companyFinder)
    {
        $this->articleFinder = $articleFinder;
        $this->courseFinder = $courseFinder;
        $this->jobFinder = $jobFinder;
        $this->companyFinder = $companyFinder;
    }

    #[Route('/ajax/elastic-search', name: 'app_search')]
    public function index(Request $request, LanguageHelper $helper, ElasticSearchHelper $searchHelper): JsonResponse
    {
        // FormData
        $query = '*' .$request->get('search') . '*';
        $county = empty($request->get('county')) ? '' : $request->get('county');
        $limit = $request->get('limit');
        $page = $request->get('page', 1);
        $locale = $helper->getLanguageByLocale($request->get('locale', $this->getParameter('default_locale')));

        if (empty($query) || empty($limit) || empty($locale)) {
            return new JsonResponse([
                'status' => false,
                'message' => 'One or more parameters are invalid.'
            ]);
        }

        // Get articles by search params
        $articles = $searchHelper->searchAction(
            $this->articleFinder,
            $query,
            $locale->getLocale(),
            $searchHelper::ARTICLE_QUERY_FIELDS['fields'],
            $searchHelper::ARTICLE_QUERY_FIELDS['translationField'],
            $limit,
            $page
        );

        // Get courses by search params
        $courses = $searchHelper->searchAction(
            $this->courseFinder,
            $query,
            $locale->getLocale(),
            $searchHelper::COURSE_QUERY_FIELDS['fields'],
            $searchHelper::COURSE_QUERY_FIELDS['translationField'],
            $limit,
            $page,
            $county
        );

        // Get jobs by search params
        $jobs = $searchHelper->searchAction(
            $this->jobFinder,
            $query,
            $locale->getLocale(),
            $searchHelper::JOB_QUERY_FIELDS['fields'],
            $searchHelper::JOB_QUERY_FIELDS['translationField'],
            $limit,
            $page,
            $county
        );

        // Get company care by search params
        $companyCare = $searchHelper->searchCompany(
            $this->companyFinder,
            $query,
            Company::LOCATION_TYPE_CARE,
            $searchHelper::COMPANY_QUERY_FIELDS,
            $county,
            $limit,
            $page
        );

        // Get company care by search params
        $companyService = $searchHelper->searchCompany(
            $this->companyFinder,
            $query,
            Company::LOCATION_TYPE_PROVIDER,
            $searchHelper::COMPANY_QUERY_FIELDS,
            $county,
            $limit,
            $page
        );

        return new JsonResponse([
            'status' => true,
            'companyCares' => [
                'rows' => $companyCare['data'],
                'total' => $companyCare['total'],
                'page' => $companyCare['page'],
                'totalPages' => $companyCare['pages']
            ],
            'companyServices' => [
                'rows' => $companyService['data'],
                'total' => $companyService['total'],
                'page' => $companyService['page'],
                'totalPages' => $companyService['pages']
            ],
            'articles' => [
                'rows' => $articles['data'],
                'total' => $articles['total'],
                'page' => $articles['page'],
                'totalPages' => $articles['pages']
            ],
            'courses' => [
                'rows' => $courses['data'],
                'total' => $courses['total'],
                'page' => $courses['page'],
                'totalPages' => $courses['pages']
            ],
            'jobs' => [
                'rows' => $jobs['data'],
                'total' => $jobs['total'],
                'page' => $jobs['page'],
                'totalPages' => $jobs['pages']
            ]
        ]);
    }

    #[Route('/ajax/elastic-search-cares', name: 'ajax_search_cares')]
    public function searchCares(Request $request, ElasticSearchHelper $searchHelper): JsonResponse
    {
        $keyword = $request->get('search');
        $searchBy = $request->get('searchBy');
        $limit = $request->get('limit', 4);
        $countyCode = '';

        if (empty($keyword) || empty($searchBy)) {
            return new JsonResponse([
                'status' => false,
                'rows' => [],
                'total' => 0
            ]);
        }

        // Get company care by search params
        $companyCare = $searchHelper->searchCompany(
            $this->companyFinder,
            $keyword,
            Company::LOCATION_TYPE_CARE,
            $searchBy === 'location' ? $searchHelper::COMPANY_LOCATION_QUERY_FIELDS : $searchHelper::COMPANY_QUERY_FIELDS,
            '',
            $limit
        );

        // Check exist data and parse response
        if (!empty($companyCare['data'])) {
            $countyCode = DefaultHelper::countCountyCodes($companyCare);
        }

        return new JsonResponse([
            'status' => true,
            'rows' => $companyCare['data'],
            'total' => $companyCare['total'],
            'countyCode' => $countyCode
        ]);
    }
}
