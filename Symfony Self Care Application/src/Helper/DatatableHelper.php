<?php

namespace App\Helper;

/**
 * Datatable Helper
 *
 * Utility class to handle operations on datatables.
 */
class DatatableHelper
{
    const LANG_FIELDS = [
        "id",
        "name",
        "locale"
    ];
    const USER_FIELDS = [
        "id",
        "name",
        "email",
        "enabled",
        "createdAt",
        "lastLoginAt"
    ];
    const COMPANY_FIELDS = [
        "id",
        "name",
        "email",
        "status",
        "companyFileName",
        "createdAt",
        "updatedAt"
    ];
    const REVIEW_FIELDS = [
        "id",
        "",
        "companyName",
        "email",
        "totalValuesStar",
        "",
        "status",
        "createdAt"
    ];

    const ARTICLE_FIELDS = [
        "id",
        "title",
        "slug",
        "",
        "status",
        "",
        "createdAt"
    ];
    const JOB_FIELDS = [
        "id",
        "title",
        "slug",
        "county",
        "city",
        "jobType",
        "status",
        "",
        "createdAt"
    ];
    const TRAINING_FIELDS = [
        "id",
        "title",
        "slug",
        "county",
        "city",
        "price",
        "status",
        "",
        "createdAt"
    ];
    const DEFAULT_CATEGORY_FIELDS = [
        "id",
        "title",
        "slug",
        "status",
        "createdAt"
    ];
    const MENU_FIELDS = [
        "id",
        "title",
        "machineName",
        "createdAt"
    ];
    const PAGE_FIELDS = [
        "id",
        "name",
        "machineName",
        "url",
        "createdAt"
    ];

    /**
     * @param array $params
     * @param array $fields
     * @param string $defVal
     * @return array
     */
    public function getTableParams(array $params = [], array $fields = [], string $defVal = 'id'): array
    {
        $key = 0;
        $dir = 'DESC';
        $keyword = null;
        $startDate = null;
        $endDate = null;

        // Check and set order
        if (isset($params['order'][0]['column']) && $params['order'][0]['dir']) {
            $key = $params['order'][0]['column'];
            $dir = $params['order'][0]['dir'];
        }

        // Check and set search values
        if (isset($params['search']['value']) && $params['search']['value']) {
            $keyword = $params['search']['value'];
        }

        if (isset($params['startDate']) && isset($params['endDate'])) {
            $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', $params['startDate']);
            $endDate = \DateTime::createFromFormat('Y-m-d H:i:s', $params['endDate']);
        }

        return [
            'keyword' => $keyword,
            'column' => array_key_exists($key, $fields) ? $fields[$key] : $defVal,
            'dir' => $dir,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
    }
}
