<?php

namespace App\Helper;

use App\Entity\Article;
use App\Entity\Company;
use App\Entity\EntityDisplayLog;
use App\Entity\Job;
use App\Entity\MembershipPackage;
use App\Entity\TrainingCourse;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MembershipHelper
{
    const FILTER_COMPANY_RECOMMENDED = 'company-recommended';
    const FILTER_COMPANY_CATEGORY_RECOMMENDED = 'company-category-recommended';
    const FILTER_COMPANY_COUNTY_RECOMMENDED = 'company-county-recommended';
    const FILTER_JOB_RECOMMENDED = 'job-recommended';
    const FILTER_COURSE_RECOMMENDED = 'course-recommended';

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var Security
     */
    protected Security $security;

    /**
     * @var LanguageHelper
     */
    protected LanguageHelper $languageHelper;

    public function __construct(EntityManagerInterface $em, LanguageHelper $languageHelper, Security $security)
    {
        $this->em = $em;
        $this->languageHelper = $languageHelper;
        $this->security = $security;
    }

    /**
     * @param User $user
     * @param string $entity
     * @return array
     */
    public function checkMembership(User $user, string $entity): array
    {
        $max = 0;
        $total = 0;

        // Check @role
        if ($user->hasRole('ROLE_COMPANY')) {
            /** @var MembershipPackage $membership */
            $membership = $user->getMembershipPackage();

            // Check user membership plan
            if (empty($membership)) {
                return [
                    'status' => true,
                    'membership' => MembershipPackage::PACKAGE_FREE,
                    'max' => $max
                ];
            }

            switch ($entity) {
                case Job::ENTITY_NAME:
                    /** @var Job $total */
                    $total = $this->em->getRepository(Job::class)->countByUserForCurrentMonth($user);
                    $max = $membership->getMaxJobPerMonth();
                    break;
                case TrainingCourse::ENTITY_NAME:
                    /** @var TrainingCourse $total */
                    $total = $this->em->getRepository(TrainingCourse::class)->countByUserForCurrentMonth($user);
                    $max = $membership->getMaxCoursePerMonth();
                    break;
                case Article::ENTITY_NAME:
                    /** @var Article $total */
                    $total = $this->em->getRepository(Article::class)->countByUserForCurrentMonth($user);
                    $max = $membership->getMaxArticlePerMonth();
                    break;
                case Article::ENTITY_AI_NAME:
                    /** @var Article $total */
                    $total = $this->em->getRepository(Article::class)->countByUserForCurrentMonth($user, true);
                    $max = $membership->getMaxGenerateArticlePerMonth();
                    break;
            }

            return [
                'status' => $total >= $max,
                'membership' => $membership->getTranslation($this->languageHelper->getDefaultLanguage()->getLocale())->getName(),
                'max' => $max
            ];
        }

        return ['status' => false];
    }

    /**
     * @param $items
     * @param string $entityName
     * @param bool $isArray
     * @return void
     */
    public function parseEntityLog($items, string $entityName, bool $isArray = true): void
    {
        $entityClass = match ($entityName) {
            Article::ENTITY_NAME => Article::class,
            Job::ENTITY_NAME => Job::class,
            TrainingCourse::ENTITY_NAME => TrainingCourse::class,
            Company::ENTITY_NAME => Company::class,
        };

        /** @var $item */
        foreach ($items as $item) {
            $getEntity = $this->em->getRepository($entityClass)->find($isArray ? $item['id'] : $item->getId());

            // Check exist object
            if (!empty($getEntity)) {
                $this->insertEntityLog($getEntity, $entityName);
            }
        }
    }

    /**
     * @param $object
     * @param string $entityName
     * @return void
     */
    public function insertEntityLog($object, string $entityName): void
    {
        /** @var EntityDisplayLog $entityLog */
        $entityLog = $this->em->getRepository(EntityDisplayLog::class)->findOneBy([
            $entityName => $object
        ]);

        // Check exist item and increment
        if (!empty($entityLog)) {
            $entityLog->incrementDisplayCount();
        }

        // Check exist entityLog
        $entityLog = $entityLog ?? new EntityDisplayLog();

        match ($entityName) {
            Article::ENTITY_NAME => $entityLog->setArticle($object),
            Job::ENTITY_NAME => $entityLog->setJob($object),
            TrainingCourse::ENTITY_NAME => $entityLog->setCourse($object),
            Company::ENTITY_NAME => $entityLog->setCompany($object),
        };

        $entityLog->setEntity($entityName === Company::ENTITY_NAME ? $object->getLocationType() : $entityName);
        $entityLog->setDisplayedAt(new \DateTime());

        // persist and save
        $this->em->persist($entityLog);
        $this->em->flush();
    }

    /**
     * @param array $packages
     * @param array $excludePackages
     * @param int $limit
     * @return array
     */
    public function filterDataByPackages(array $packages, array $excludePackages, int $limit): array
    {
        $i = 0;
        $finalResults = [];
        $totalResults = 0;
        $remainingLimit = $limit;

        $allPackages = $packages;

        // Loop through the packages to exclude and remove them
        $packages = MembershipPackage::getPackagesExcluding($packages, $excludePackages);

        // Filter packages to include only those with items
        $validPackages = array_filter($packages, function ($items) {
            return !empty($items);
        });

        // Calculate how many packages we have
        $packageCount = count($validPackages);
        $totalElements = array_sum(array_map('count', $packages));

        // Check empty array
        if ($packageCount > 0) {
            // Calculate the equal limit per package
            $perPackageLimit = floor($limit / $packageCount);

            // Loop through each package
            foreach ($packages as $package) {
                $packageLimit = $perPackageLimit;
                if ($totalElements >= $limit && $i === 0) {
                    $packageLimit = $limit > 4 ? 3 : 2;
                    $i++;
                }

                // Take the minimum between the number of results available and the calculated limit per package
                $takeFromPackage = min(count((array)$package), $packageLimit);

                // Add results from the current package to the final array
                $finalResults = array_merge($finalResults, array_slice((array)$package, 0, $takeFromPackage));

                // Update the remaining limit and total number of results
                $remainingLimit -= $takeFromPackage;
                $totalResults += $takeFromPackage;
            }
        }

        // If there are still remaining limits, take from excluded packages
        if ($remainingLimit > 0) {
            foreach ($excludePackages as $package) {
                if (isset($allPackages[$package])) {
                    // Get items from the excluded package
                    $itemsFromExcluded = $allPackages[$package];

                    // Calculate how many items to take
                    $itemsToTake = min(count((array)$itemsFromExcluded), $remainingLimit);
                    $finalResults = array_merge((array)$finalResults, array_slice((array)$itemsFromExcluded, 0, $itemsToTake));

                    // Decrease the remaining limit
                    $remainingLimit -= $itemsToTake;

                    // If the remaining limit is 0, break the loop
                    if ($remainingLimit <= 0) {
                        break;
                    }
                }
            }
        }

        return $finalResults;
    }

    /**
     * @param string $type
     * @param string $entityName
     * @param array $excludePackages
     * @param array $filterParams
     * @param int $limit
     * @return array
     */
    public function filterDataByPackage(string $type, string $entityName, array $excludePackages, array $filterParams, int $limit): array
    {
        $items = [];

        $companyRepository = $this->em->getRepository(Company::class);
        $jobRepository = $this->em->getRepository(Job::class);
        $courseRepository = $this->em->getRepository(TrainingCourse::class);

        $category = $filterParams['category'] ?? null;
        $locationType = $filterParams['locationType'] ?? null;
        $company = $filterParams['company'] ?? null;

        $language = $filterParams['language'] ?? null;
        $job = $filterParams['job'] ?? null;
        $course = $filterParams['course'] ?? null;
        $offset = $filterParams['offset'] ?? 0;

        // Parse and call by @package
        foreach (MembershipPackage::getPackages() as $package) {
            switch ($type) {
                case self::FILTER_COMPANY_RECOMMENDED:
                    /**
                     * Get items by @locationType and @category
                     */
                    $result = $companyRepository->getCompaniesByType($locationType, $category, $limit, $package);

                    // Store results in array by @package
                    $items[$package] = $result;
                    break;
                case self::FILTER_COMPANY_CATEGORY_RECOMMENDED:
                    /**
                     * Get items by @company and @isCategory
                     */
                    $result = $companyRepository->getCompaniesByCategoryOrCounty($company, $package, true, false, $limit);

                    // Store results in array by @package
                    $items[$package] = $result;
                    break;
                case self::FILTER_COMPANY_COUNTY_RECOMMENDED:
                    /**
                     * Get items by @company and @isCounty
                     */
                    $result = $companyRepository->getCompaniesByCategoryOrCounty($company, $package, false, true, $limit);

                    // Store results in array by @package
                    $items[$package] = $result;
                    break;

                case self::FILTER_JOB_RECOMMENDED:
                    /**
                     * Get items by @company and @isCounty
                     */
                    $result = $jobRepository->getRecommendedJobs($language, $package, $job, $limit, $offset);

                    // Store results in array by @package
                    $items[$package] = $result;
                    break;
                case self::FILTER_COURSE_RECOMMENDED:
                    /**
                     * Get items by @company and @isCounty
                     */
                    $result = $courseRepository->getRecommendedCourses($language, $package, $course, $limit, $offset);
                    // Store results in array by @package
                    $items[$package] = $result;
                    break;
            }
        }

        $rows = $this->filterDataByPackages($items, $excludePackages, $limit);

        // Parse and increment entityLog
        $this->parseEntityLog($rows, $entityName);

        return $rows;
    }

    /**
     * @param string $entityName
     * @param User|null $user
     * @return int
     */
    public function changeEntitiesStatus(string $entityName = Article::ENTITY_NAME, ?User $user = null): int
    {
        $total = 0;

        $items = match ($entityName) {
            Article::ENTITY_NAME => $this->em->getRepository(Article::class)->getExpireItems($user),
            Job::ENTITY_NAME => $this->em->getRepository(Job::class)->getExpireItems($user),
            TrainingCourse::ENTITY_NAME => $this->em->getRepository(TrainingCourse::class)->getExpireItems($user)
        };

        /**
         * Parse and change status
         */
        foreach ($items as $item) {
            $item->setStatus(DefaultHelper::STATUS_DRAFT);
            $total++;

            // Persist and save
            $this->em->persist($item);
            $this->em->flush();
        }

        return $total;
    }
}