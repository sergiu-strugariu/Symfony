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
     * Parses the response to add new keys based on the package slug.
     *
     * @param array $packages List of packages (MembershipPackage)
     * @return array Modules with updated options
     */
    public function parseResponse(array $packages): array
    {
        $modules = MembershipPackage::MODULES;

        /** @var MembershipPackage $package */
        foreach ($packages as $package) {
            $slug = $package->getSlug();

            // Iterate through all modules defined in MembershipPackage::MODULES
            foreach ($modules as $moduleKey => &$module) {
                // Add options based on the module and slug
                switch ($moduleKey) {
                    case MembershipPackage::MODULE_ADMINISTRATIVE:
                        $module['packages'][$slug] = $package->getAdministrativeModule();
                        break;
                    case MembershipPackage::MODULE_MEDICAL:
                        $module['packages'][$slug] = $package->getMedicalModule();
                        break;
                    case MembershipPackage::MODULE_PHYSIOTHERAPY:
                        $module['packages'][$slug] = $package->getPhysiotherapyModule();
                        break;
                    case MembershipPackage::MODULE_INFIRMARY:
                        $module['packages'][$slug] = $package->getInfirmaryModule();
                        break;
                    case MembershipPackage::MODULE_RECEPTION:
                        $module['packages'][$slug] = $package->getReceptionModule();
                        break;
                    case MembershipPackage::MODULE_KITCHEN:
                        $module['packages'][$slug] = $package->getKitchenModule();
                        break;
                }
            }
        }

        return $modules;
    }
}