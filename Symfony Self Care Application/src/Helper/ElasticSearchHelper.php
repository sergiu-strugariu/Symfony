<?php

namespace App\Helper;

use App\Entity\Company;
use Elastica\Query;
use Elastica\ResultSet;
use FOS\ElasticaBundle\Elastica\Index;
use Elastica\Query\BoolQuery;
use Elastica\Query\Nested;
use Elastica\Query\MultiMatch;
use Elastica\Query\Term;
use Elastica\Query\Exists;

class ElasticSearchHelper
{
    const ARTICLE_QUERY_FIELDS = [
        'translationField' => 'articleTranslations',
        'fields' => [
            'articleTranslations.title',
            'articleTranslations.body',
            'articleTranslations.shortDescription'
        ]
    ];

    const COURSE_QUERY_FIELDS = [
        'translationField' => 'trainingCourseTranslations',
        'fields' => [
            'trainingCourseTranslations.title',
            'trainingCourseTranslations.body',
            'trainingCourseTranslations.shortDescription'
        ]
    ];

    const JOB_QUERY_FIELDS = [
        'translationField' => 'jobTranslations',
        'fields' => [
            'jobTranslations.title',
            'jobTranslations.body',
            'jobTranslations.shortDescription'
        ]
    ];

    const COMPANY_QUERY_FIELDS = [
        'name',
        'slug',
        'availableServices'
    ];

    const COMPANY_LOCATION_QUERY_FIELDS = [
        'countyName',
        'cityName',
        'address'
    ];

    /**
     * @param Index $finder
     * @param string $searchTerm
     * @param string $locale
     * @param array $fields
     * @param string $translationField
     * @param int $limit
     * @param int $page
     * @param string $countyCode
     * @param string $searchType
     * @return array
     */
    public function searchAction(Index $finder, string $searchTerm, string $locale, array $fields, string $translationField, int $limit = 5, int $page = 1, string $countyCode = '', string $searchType = 'phrase_prefix'): array
    {
        // Create a BoolQuery object for the main query
        $boolQuery = new BoolQuery();

        // Create a Nested object for searching nested fields
        $nestedQuery = new Nested();
        $nestedQuery->setPath($translationField);

        // Create a BoolQuery object for the nested query
        $nestedBoolQuery = new BoolQuery();

        // Create a MultiMatch object for searching the specified fields
        $multiMatch = new MultiMatch();
        $multiMatch->setQuery($searchTerm);
        $multiMatch->setType($searchType);
        $multiMatch->setFields($fields);
        $multiMatch->setOperator('and');


        // Add the MultiMatch query to the nested BoolQuery query
        $nestedBoolQuery->addMust($multiMatch);

        // Adding a language filter @locale
        $languageTermFilter = new Term();
        $languageTermFilter->setTerm($translationField . '.languageLocale', $locale);
        $nestedBoolQuery->addFilter($languageTermFilter);

        // Add the nested BoolQuery to the Nested query
        $nestedQuery->setQuery($nestedBoolQuery);

        // Add the Nested query to the must in the main BoolQuery query
        $boolQuery->addMust($nestedQuery);

        // Add a filter for @published status
        $statusTermFilter = new Term();
        $statusTermFilter->setTerm('status', Company::STATUS_PUBLISHED);
        $boolQuery->addFilter($statusTermFilter);

        if (!empty($countyCode)) {
            // Add nested field by @county.code
            $countyTerm = new Term();
            $countyTerm->setTerm('county.code', $countyCode);
            $nestedCountyQuery = new Nested();
            $nestedCountyQuery->setPath('county');
            $nestedCountyQuery->setQuery($countyTerm);
            $boolQuery->addFilter($nestedCountyQuery);
        }

        // Add a must_not to exclude documents with the deletedAt field
        $deletedAtExistsFilter = new Exists('deletedAt');
        $boolQuery->addMustNot($deletedAtExistsFilter);

        // Create the main query with BoolQuery
        $query = new Query($boolQuery);

        // Add descending sort by @id
        $query->setSort(['_id' => ['order' => 'desc']]);

        // Calculate the offset (from) based on the current page
        $offset = ($page - 1) * $limit;

        // Set the offset and limit (size) for pagination
        $query->setFrom($offset);
        $query->setSize($limit);

        // Execute the search query
        $resultSet = $finder->search($query);

        return [
            'data' => $this->formatElasticaResultSet($resultSet, true, $locale, $translationField),
            'total' => $resultSet->getTotalHits(),
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($resultSet->getTotalHits() / $limit)
        ];
    }

    /**
     * @param Index $finder
     * @param string $searchTerm
     * @param string $locationType
     * @param array $fields
     * @param string $countyCode
     * @param int $limit
     * @param int $page
     * @param string $searchType
     * @return array
     */
    public function searchCompany(Index $finder, string $searchTerm, string $locationType, array $fields, string $countyCode, int $limit = 5, int $page = 1, string $searchType = 'phrase_prefix')
    {
        // Create a BoolQuery object for the main query
        $boolQuery = new BoolQuery();

        // Create a MultiMatch object for searching the specified fields
        $multiMatch = new MultiMatch();
        $multiMatch->setQuery($searchTerm);
        $multiMatch->setFields($fields);
        $multiMatch->setType($searchType);
        $multiMatch->setOperator('and');

        // Add the MultiMatch query to must
        $boolQuery->addMust($multiMatch);

        // Add a filter for the "enabled" field to be true
        $enabledFilter = new Term();
        $enabledFilter->setTerm('status', Company::STATUS_PUBLISHED);
        $boolQuery->addFilter($enabledFilter);

        // Add a filter for the "type"
        $typeFilter = new Term();
        $typeFilter->setTerm('locationType', $locationType);
        $boolQuery->addFilter($typeFilter);

        if (!empty($countyCode)) {
            // Create a Nested object for the nested query on the "county" field
            $nestedQuery = new Nested();
            $nestedQuery->setPath('county');
            $countyFilter = new Term();
            $countyFilter->setTerm('county.code', $countyCode);
            $nestedQuery->setQuery($countyFilter);
            $boolQuery->addFilter($nestedQuery);
        }

        // Add a must_not to exclude documents with the "deletedAt"
        $existsQuery = new Exists('deletedAt');
        $boolQuery->addMustNot($existsQuery);

        // We create the main query with BoolQuery
        $query = new Query($boolQuery);

        // Add descending sort by @id
        $query->setSort(['_id' => ['order' => 'desc']]);

        // Calculate the offset (from) based on the current page
        $offset = ($page - 1) * $limit;

        // Set the offset and limit (size) for pagination
        $query->setFrom($offset);
        $query->setSize($limit);

        // Execute the search query
        $resultSet = $finder->search($query);

        return [
            'data' => $this->formatElasticaResultSet($resultSet),
            'total' => $resultSet->getTotalHits(),
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($resultSet->getTotalHits() / $limit)
        ];
    }


    /**
     * @param ResultSet $resultSet
     * @param string $locale
     * @param string $translationField
     * @param bool $hasTranslation
     * @return array
     */
    protected function formatElasticaResultSet(ResultSet $resultSet, bool $hasTranslation = false, string $locale = '', string $translationField = ''): array
    {
        $formattedResults = [];

        foreach ($resultSet as $result) {
            $source = $result->getSource();

            if ($hasTranslation) {
                // Filter and keep only the translation that matches the specified locale
                $filteredTranslations = array_values(array_filter($source[$translationField], function ($translation) use ($locale) {
                    return $translation['languageLocale'] === $locale;
                }));

                if (!empty($filteredTranslations)) {
                    // Replace the array of translations with the filtered one
                    $source[$translationField] = $filteredTranslations[0];
                    $formattedResults[] = $source;
                }
            } else {
                $formattedResults[] = $source;
            }
        }

        return $formattedResults;
    }
}