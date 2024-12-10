<?php

namespace App\Controller\Dashboard;

use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Form\MenuItemFormType;
use App\Helper\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuItemController extends AbstractController
{
    /**
     * @Route("/dashboard/menu/item/{uuid}/view", name="dashboard_menu_items_index")
     */
    public function index(EntityManagerInterface $em, Request $request, $uuid): Response
    {
        /**
         * Get menu by @uuid
         * @var Menu $menu
         */
        $menu = $em->getRepository(Menu::class)->findOneBy(['uuid' => $uuid]);

        if (null === $menu) {
            // Set flash message
            $this->addFlash('danger', 'Acest conținut nu există');

            return $this->redirectToRoute('dashboard_menu_index');
        }

        // init form & handle request data
        $form = $this->createForm(MenuItemFormType::class);
        $form->handleRequest($request);

        return $this->render('dashboard/menu/menu-item/actions.html.twig', [
            'uuid' => $uuid,
            'pageTitle' => 'Gestionarea link-urilor pentru: ' . $menu->getTitle(),
            'menuTitle' => $menu->getTitle(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/menu/item/{uuid}/create", name="dashboard_menu_item_create")
     */
    public function create(EntityManagerInterface $em, Request $request, FileUploader $fileUploader, $uuid): Response
    {
        $errors = [];
        $parentId = $request->get('menu-item-parent');

        $getParentMenuItem = null;

        /**
         * Get menu by @uuid
         * @var Menu $menu
         */
        $menu = $em->getRepository(Menu::class)->findOneBy(['uuid' => $uuid]);

        // Check exist menu
        if (null === $menu) {
            return new JsonResponse([
                'success' => false,
                'fields' => [],
                'message' => 'Acest conținut nu există'
            ]);
        }

        // Init form & handle request data
        $menuItem = new MenuItem();
        $form = $this->createForm(MenuItemFormType::class, $menuItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get data from the file
            $file = $form->get('icon')->getData();

            // Check select parent
            if (isset($parentId)) {
                /**
                 * Get parent by @id
                 * @var MenuItem $getMenuItem
                 */
                $getParentMenuItem = $em->getRepository(MenuItem::class)->find($parentId);
            }

            // Check and set icon menu
            if (isset($file)) {
                // Upload menu icon file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_menu_path')
                );

                // Check and set @filename
                if ($uploadFile['success']) {
                    $menuItem->setIcon($uploadFile['fileName']);
                }
            }

            // Set data
            $menuItem->setMenu($menu);
            $menuItem->setMenuItem($getParentMenuItem ?? null);

            $menuItem->setLink($form->get('link')->getData());
            $menuItem->setLinkText($form->get('linkText')->getData());
            $menuItem->setDescription($form->get('description')->getData() ?? null);

            $em->persist($menuItem);
            $em->flush();
        } else {
            // Check and set errors
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }

            return new JsonResponse([
                'success' => false,
                'errors' => $errors,
                'message' => 'Verificați erorile de formular.'
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'errors' => [],
            'message' =>  'Felicitări, ați adăugat cu succes un element nou'
        ]);
    }

    /**
     * @Route("/dashboard/menu/item/{uuid}/edit", name="dashboard_menu_item_edit")
     */
    public function edit(EntityManagerInterface $em, Request $request, FileUploader $fileUploader, $uuid): Response
    {
        $errors = [];
        $parentId = $request->get('menu-item-parent');
        $getMenuItem = null;


        /**
         * Get menu by @uuid
         * @var Menu $menu
         */
        $menu = $em->getRepository(Menu::class)->findOneBy(['uuid' => $uuid]);

        // Check exist menu
        if (null === $menu) {
            return new JsonResponse([
                'success' => false,
                'fields' => [],
                'message' => 'Acest conținut nu există'
            ]);
        }

        // init form & handle request data
        $form = $this->createForm(MenuItemFormType::class);
        $form->handleRequest($request);

        /** @var MenuItem $menuItem */
        $menuItem = $em->getRepository(MenuItem::class)->find($form->get('parentId')->getData());

        // Check exist menu
        if (null === $menuItem) {
            return new JsonResponse([
                'success' => false,
                'fields' => [],
                'message' => 'Acest conținut nu există'
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Get data from the file
            $file = $form->get('icon')->getData();

            // Check and set parent
            if (isset($parentId)) {
                /**
                 * Get parent by @id
                 * @var MenuItem $getMenuItem
                 */
                $getMenuItem = $em->getRepository(MenuItem::class)->find($parentId);
            }

            // Check and set icon menu
            if (isset($file)) {
                // Upload menu icon file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_menu_path')
                );

                // Check and set @filename
                if ($uploadFile['success']) {
                    $menuItem->setIcon($uploadFile['fileName']);
                }
            }

            $menuItem->setCssClass($form->get('cssClass')->getData() ?? null);
            $menuItem->setMenu($menu);
            $menuItem->setMenuItem($getMenuItem ?? null);

            $menuItem->setLink($form->get('link')->getData());
            $menuItem->setLinkText($form->get('linkText')->getData());
            $menuItem->setDescription($form->get('description')->getData() ?? null);

            // Parse and save menuItemTranslation
            $em->persist($menuItem);
            $em->flush();
        } else {
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }

            return new JsonResponse([
                'success' => false,
                'errors' => $errors,
                'message' => 'Verificați erorile de formular'
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'errors' => [],
            'message' => 'Acest conținut a fost actualizat cu succes'
        ]);
    }

    /**
     * @Route("/dashboard/menu/item/{uuid}/update-node", name="dashboard_menu_item_update_node")
     */
    public function updateNode(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $orders = $request->get('order');
        $menuItemId = $request->get('menu-item-id');
        $menuItemParent = $request->get('menu-item-parent');

        if (!isset($orders) && !isset($menuItemId)) {
            return new JsonResponse([
                'success' => false,
                'message' =>'Oops! Ceva nu a mers bine'
            ]);
        }

        $menuParent = $em->getRepository(MenuItem::class)->find($menuItemParent);
        $menuItem = $em->getRepository(MenuItem::class)->find($menuItemId);

        // Check exist menu item
        if (isset($menuItem)) {
            $menuItem->setMenuItem($menuParent ?? null);

            $em->persist($menuItem);
            $em->flush();
        }

        // Listing orders
        foreach ($orders as $orderItem) {
            $getMenuItem = $em->getRepository(MenuItem::class)->find($orderItem['id']);
            $getMenuItem->setWeight($orderItem['order']);
            $em->persist($getMenuItem);
            $em->flush();
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Acest conținut a fost actualizat cu succes'
        ]);
    }

    /**
     * @Route("/dashboard/menu/item/{uuid}/delete", name="dashboard_menu_item_remove")
     */
    public function delete(Request $request, EntityManagerInterface $em, FileUploader $fileUploader): JsonResponse
    {
        // init form & handle request data
        $form = $this->createForm(MenuItemFormType::class);
        $form->handleRequest($request);

        $parentId = $form->get('parentId')->getData();

        $menuItem = $em->getRepository(MenuItem::class)->find($parentId);
        $icon = $menuItem->getIcon();

        if (!isset($menuItem)) {
            return new JsonResponse([
                'success' => false,
                'message' =>'Acest conținut nu există'
            ]);
        }

        // Check exist icon
        if (isset($icon)) {
            try {
                //Remove icon for the filesystem
                $fileUploader->removeFile($this->getParameter('app_menu_path'), $menuItem->getIcon());
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }

        // Reset items and remove item translations
        $menuItem->resetMenuItems();
        $em->remove($menuItem);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Acest conținut a fost șters cu succes'
        ]);
    }
}
