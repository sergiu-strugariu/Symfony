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
        if ($this->security->isGranted('ROLE_COMPANY')) {
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
                    /** @var Job $getTotalCreated */
                    $total = $this->em->getRepository(Job::class)->countByUserForCurrentMonth($user);
                    $max = $membership->getMaxJobPerMonth();
                    break;
                case Article::ENTITY_NAME:
                    /** @var Article $getTotalCreated */
                    $total = $this->em->getRepository(Article::class)->countByUserForCurrentMonth($user);
                    $max = $membership->getMaxArticlePerMonth();
                    break;
                case Article::ENTITY_AI_NAME:
                    /** @var Article $getTotalCreated */
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

        // Check empty array
        if ($packageCount > 0) {
            // Calculate the equal limit per package
            $perPackageLimit = floor($limit / $packageCount);

            // Loop through each package
            foreach ($packages as $package) {
                // Take the minimum between the number of results available and the calculated limit per package
                $takeFromPackage = min(count((array)$package), $perPackageLimit);

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
}