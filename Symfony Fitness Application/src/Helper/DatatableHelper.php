<?php

namespace App\Helper;

/**
 * Datatable Helper
 *
 * Utility class to handle operations on datatables.
 */
class DatatableHelper
{
    const USER_FIELDS = [
        "id",
        "firstName",
        "email",
        "enabled",
        "createdAt",
        "lastLoginAt"
    ];
    
    const EDUCATION_FIELDS = [
        "id",
        "title",
        "price",
        "vat",
        "startDate",
        "endDate"
    ];

    const ARTICLE_FIELDS = [
        "id",
        "title",
        "status"
    ];

    const LEAD_FIELDS = [
        "id",
        "firstName",
        "lastName",
        "email",
        "phone",
        "companyDetails",
        "interests"
    ];
    
    const TEAM_MEMBER_FIELDS = [
        "id",
        "name"
    ];
    
    const GALLERY_FIELDS = [
        "id",
        "title",
        "status"
    ];
    
    const EDUCATION_REPOSITORY_FIELDS = [
        "id",
        "firstName",
        "lastName",
        "email",
        "phone",
        "paymentAmount",
        "paymentStatus",
    ];

    const LANGUAGE_FIELDS = [
        "id",
        "name",
        "locale"
    ];

    const MENU_FIELDS = [
        "id",
        "title",
        "links",
        "machineName"
    ];

    const PAGE_FIELDS = [
        "id",
        "name",
        "machineName",
        "url",
        "createdAt"
    ];
    
    const FEEDBACK_FIELDS = [
        "id",
        "firstName",
        "title",
        "answeredAt"
    ];

    const CERTIFICATION_FIELDS = [
        "id",
        "title",
        "description"
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
