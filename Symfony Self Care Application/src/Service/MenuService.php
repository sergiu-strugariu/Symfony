<?php

namespace App\Service;

use App\Entity\MenuItem;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MenuService extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var LanguageHelper
     */
    protected LanguageHelper $languageHelper;

    public function __construct(EntityManagerInterface $em, LanguageHelper $languageHelper)
    {
        $this->em = $em;
        $this->languageHelper = $languageHelper;
    }

    /**
     * @param $locale
     * @param $machineName
     * @return array
     */
    public function menuItems($locale, $machineName): array
    {
        $menuItems = $this->em->getRepository(MenuItem::class)->findByWeightDesc(
            $this->languageHelper->getLanguageByLocale($locale),
            $machineName
        );

        return $this->buildMenuTree($menuItems);
    }

    /**
     * @param $menuItems
     * @param $parentId
     * @return array
     */
    private function buildMenuTree($menuItems, $parentId = null): array
    {
        $tree = [];

        foreach ($menuItems as $menuItem) {
            if ($menuItem['menu_item_id'] === $parentId) {
                $menuItem['children'] = $this->buildMenuTree($menuItems, $menuItem['id']);
                $tree[] = $menuItem;
            }
        }

        return $tree;
    }
}