<?php

namespace App\Service;

use App\Entity\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MenuService extends AbstractController
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
     * @param $machineName
     * @return array
     */
    public function menuItems($machineName): array
    {
        $menuItems = $this->em->getRepository(MenuItem::class)->findByWeightDesc(
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