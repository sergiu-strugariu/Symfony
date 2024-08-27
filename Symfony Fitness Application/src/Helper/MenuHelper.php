<?php

namespace App\Helper;

use App\Entity\Menu;
use App\Entity\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MenuHelper extends AbstractController
{
    private EntityManagerInterface $em;
    private LanguageHelper $languageHelper;

    public function __construct(EntityManagerInterface $em, LanguageHelper $languageHelper)
    {
        $this->em = $em;
        $this->languageHelper = $languageHelper;
    }

    public function menuItems($locale, $name)
    {
        $language = $this->languageHelper->getLanguageByLocale($locale);
        $menu = $this->em->getRepository(Menu::class)->findOneBy(['machineName' => $name]);

        if (null === $menu) {
            $menuItems = $this->em->getRepository(MenuItem::class)->findByWeightDesc($language);
            return $this->buildMenuTree($menuItems);
        }

        $menuItems = $this->em->getRepository(MenuItem::class)->findByWeightDesc($language, $menu->getUuid());
        return $this->buildMenuTree($menuItems);
    }
    
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