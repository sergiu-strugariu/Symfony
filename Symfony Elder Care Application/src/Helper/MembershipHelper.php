<?php

namespace App\Helper;

use App\Entity\MembershipPackage;
use Doctrine\ORM\EntityManagerInterface;

class MembershipHelper
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $response
     * @return array
     */
    public function parseCheckboxResponse($response): array
    {
        $modules = MembershipPackage::MODULES;

        foreach ($response as $moduleKey => $features) {
            if (isset($modules[$moduleKey])) {
                foreach ($features as $featureKey => $value) {
                    if (isset($modules[$moduleKey][$featureKey])) {
                        $modules[$moduleKey][$featureKey]['enabled'] = ($value == 'on');
                    }
                }
            }
        }

        return $modules;
    }

    /**
     * @param $packages
     * @return array
     */
    public function parseResponse($packages): array
    {
        $membership = MembershipPackage::MODULES;

        $administrative = [];
        $medical = [];
        $physiotherapy = [];
        $infirmary = [];
        $reception = [];
        $kitchen = [];

        /** @var MembershipPackage $package */
        foreach ($packages as $package) {
            foreach ($package->getAdministrativeModule() as $key => $item) {
                // Skip this item
                if ($key === 'title') continue;
                $administrative = self::moduleItem($administrative, $item, $package->getSlug());
            }

            foreach ($package->getMedicalModule() as $key => $item) {
                // Skip this item
                if ($key === 'title') continue;
                $medical = self::moduleItem($medical, $item, $package->getSlug());
            }

            foreach ($package->getPhysiotherapyModule() as $key => $item) {
                // Skip this item
                if ($key === 'title') continue;
                $physiotherapy = self::moduleItem($physiotherapy, $item, $package->getSlug());
            }

            foreach ($package->getInfirmaryModule() as $key => $item) {
                // Skip this item
                if ($key === 'title') continue;
                $infirmary = self::moduleItem($infirmary, $item, $package->getSlug());
            }

            foreach ($package->getReceptionModule() as $key => $item) {
                // Skip this item
                if ($key === 'title') continue;
                $reception = self::moduleItem($reception, $item, $package->getSlug());
            }

            foreach ($package->getKitchenModule() as $key => $item) {
                // Skip this item
                if ($key === 'title') continue;
                $kitchen = self::moduleItem($kitchen, $item, $package->getSlug());
            }
        }

        return [
            $membership[MembershipPackage::MODULE_ADMINISTRATIVE]['title'] => $administrative,
            $membership[MembershipPackage::MODULE_MEDICAL]['title'] => $medical,
            $membership[MembershipPackage::MODULE_PHYSIOTHERAPY]['title'] => $physiotherapy,
            $membership[MembershipPackage::MODULE_INFIRMARY]['title'] => $infirmary,
            $membership[MembershipPackage::MODULE_RECEPTION]['title'] => $reception,
            $membership[MembershipPackage::MODULE_KITCHEN]['title'] => $kitchen
        ];
    }

    /**
     * @param $arr
     * @param $item
     * @param $packageSlug
     * @return mixed
     */
    protected function moduleItem($arr, $item, $packageSlug)
    {
        if (!isset($arr[$item['title']])) {
            $arr[$item['title']] = [
                'icon' => $item['icon'],
                'packages' => []
            ];
        }


        $arr[$item['title']]['packages'][$packageSlug] = $item['enabled'];

        return $arr;
    }
}