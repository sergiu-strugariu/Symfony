<?php

namespace App\Helper;

class BreadcrumbsHelper
{
    const ABOUTUS_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.benefit_title',
            'route' => 'app_benefits',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.about_title',
            'route' => null,
            'params' => []
        ]
    ];
    const BENEFIT_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.benefit_title',
            'route' => null,
            'params' => []
        ]
    ];
    const HELPLINE_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.helpline_title',
            'route' => null,
            'params' => []
        ]
    ];
    const BLOG_LISTING_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.blog_title',
            'route' => null,
            'params' => []
        ]
    ];
    const BLOG_SINGLE_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.blog_title',
            'route' => 'app_blog',
            'params' => []
        ]
    ];

    const COMPANIES_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.company_search_title',
            'route' => 'app_company',
            'params' => []
        ]
    ];
    const COMPANY_SINGLE_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.company_search_title',
            'route' => null,
            'params' => []
        ],
        [
            'name' => 'breadcrumb.company_title',
            'route' => 'app_company',
            'params' => []
        ]
    ];
    const PROVIDER_SINGLE_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.company_search_title',
            'route' => 'app_provider',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.provider_title',
            'route' => '',
            'params' => []
        ]
    ];

    const JOB_LISTING_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.job_title',
            'route' => null,
            'params' => []
        ]
    ];
    const JOB_SINGLE_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.job_title',
            'route' => 'app_jobs',
            'params' => []
        ]
    ];
    const COURSE_LISTING_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.course_title',
            'route' => null,
            'params' => []
        ]
    ];
    const COURSE_SINGLE_BREADCRUMBS = [
        [
            'name' => 'breadcrumb.homepage_title',
            'route' => 'app_homepage',
            'params' => []
        ],
        [
            'name' => 'breadcrumb.course_title',
            'route' => 'app_courses',
            'params' => []
        ]
    ];
}