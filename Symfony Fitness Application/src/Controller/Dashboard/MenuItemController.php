<?php

namespace App\Controller\Dashboard;

use App\Entity\Language;
use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Entity\MenuItemTranslation;
use App\Form\Type\MenuItemType;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MenuItemController extends AbstractController
{
    #[Route('/dashboard/menu-items/{uuid}/view', name: 'dashboard_menu_items_index')]
    public function index(EntityManagerInterface $em, Request $request, $uuid): Response
    {
        /**
         * Get menu by @uuid
         * @var Menu $menu
         */
        $menu = $em->getRepository(Menu::class)->findOneBy(['uuid' => $uuid]);

        if (null === $menu) {
            // Set flash message
            $this->addFlash('danger', 'This menu does not exist');
            return $this->redirectToRoute('dashboard_menu_index');
        }

        // init form & handle request data
        $form = $this->createForm(MenuItemType::class);
        $form->handleRequest($request);

        return $this->render('dashboard/menu/menu-item/management.html.twig', [
            'menuTitle' => $menu->getTitle(),
            'editMode' => true,
            'uuid' => $uuid,
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/menu-item/{uuid}/create', name: 'dashboard_menu_item_create')]
    public function create(EntityManagerInterface $em, Request $request, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid): Response
    {
        $errors = [];
        $parentId = $request->get('menu-item-parent');
        $getMenuItem = null;

        /**
         * Get menu by @uuid
         * @var Menu $menu
         */
        $menu = $em->getRepository(Menu::class)->findOneBy(['uuid' => $uuid]);
        $menuItem = new MenuItem();

        // Check exist menu
        if (null === $menu) {
            return new JsonResponse([
                'success' => false,
                'fields' => [],
                'message' => 'This menu does not exist'
            ]);
        }

        // init form & handle request data
        $form = $this->createForm(MenuItemType::class, $menuItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Get data from the file
            $file = $form->get('image')->getData();

            /**
             * Get all available languages
             * @var Language $languages
             */
            $languages = $languageHelper->getAllLanguages();

            // Check select parent
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

                if ($uploadFile['success']) {
                    $menuItem->setImage($uploadFile['fileName']);
                }
            }

            $menuItem->setMenu($menu);
            $menuItem->setMenuItem($getMenuItem ?? null);

            // Parse and save menuItem
            $em->persist($menuItem);
            $em->flush();

            /** @var Language $language */
            foreach ($languages as $language) {
                $menuItemTranslation = new MenuItemTranslation();
                $menuItemTranslation->setLanguage($language);
                $menuItemTranslation->setLink($form->get('link')->getData());
                $menuItemTranslation->setLinkText($form->get('linkText')->getData());
                $menuItemTranslation->setDescription($form->get('description')->getData() ?? null);
                $menuItemTranslation->setMenuItem($menuItem);

                // Parse and save menuItemTranslation
                $em->persist($menuItemTranslation);
                $em->flush();
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }

            return new JsonResponse([
                'success' => false,
                'errors' => $errors,
                'message' => 'Check form errors.'
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'errors' => [],
            'message' => 'Success created a new menu.'
        ]);
    }

    #[Route('/dashboard/menu-item/{uuid}/edit', name: 'dashboard_menu_item_edit')]
    public function edit(EntityManagerInterface $em, Request $request, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid): Response
    {
        $errors = [];
        $parentId = $request->get('menu-item-parent');
        $locale = $request->get('locale', $this->getParameter('default_locale'));
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
                'message' => 'This menu does not exist'
            ]);
        }

        // init form & handle request data
        $form = $this->createForm(MenuItemType::class);
        $form->handleRequest($request);

        /** @var MenuItem $menuItem */
        $menuItem = $em->getRepository(MenuItem::class)->find($form->get('parentId')->getData());

        // Check exist menu
        if (null === $menuItem) {
            return new JsonResponse([
                'success' => false,
                'fields' => [],
                'message' => 'This menu item does not exist'
            ]);
        }

        /**
         * @var MenuItemTranslation $menuItemTranslation
         */
        $menuItemTranslation = $em->getRepository(MenuItemTranslation::class)->findOneBy([
            'language' => $languageHelper->getLanguageByLocale($locale),
            'menuItem' => $menuItem
        ]);

        // Check exist menu
        if (null === $menuItemTranslation) {
            return new JsonResponse([
                'success' => false,
                'fields' => [],
                'message' => 'This menu item translation does not exist.'
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Get data from the file
            $file = $form->get('image')->getData();

            // Check and set parent
            if (isset($parentId)) {
                /**
                 * Get parent by @id
                 * @var MenuItem $getMenuItem
                 */
                $getMenuItem = $em->getRepository(MenuItem::class)->find($parentId);
            }

            // Check and set image menu
            if (isset($file)) {
                // Upload menu icon file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_menu_path')
                );

                if ($uploadFile['success']) {
                    $menuItem->setImage($uploadFile['fileName']);
                }
            }

            $menuItem->setCssClass($form->get('cssClass')->getData() ?? null);
            $menuItem->setMenu($menu);
            $menuItem->setMenuItem($getMenuItem ?? null);

            // Parse and save menuItem
            $em->persist($menuItem);
            $em->flush();

            $menuItemTranslation->setLanguage($languageHelper->getLanguageByLocale($locale));
            $menuItemTranslation->setLink($form->get('link')->getData());
            $menuItemTranslation->setLinkText($form->get('linkText')->getData());
            $menuItemTranslation->setDescription($form->get('description')->getData() ?? null);
            $menuItemTranslation->setMenuItem($menuItem);

            // Parse and save menuItemTranslation
            $em->persist($menuItemTranslation);
            $em->flush();
        } else {
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }

            return new JsonResponse([
                'success' => false,
                'errors' => $errors,
                'message' => 'Check form errors.'
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'errors' => [],
            'message' => 'The menu has been edited with success.'
        ]);
    }

    #[Route('/dashboard/menu-item/{uuid}/update-node', name: 'dashboard_menu_item_update_node')]
    public function updateNode(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $orders = $request->get('order');
        $menuItemId = $request->get('menu-item-id');
        $menuItemParent = $request->get('menu-item-parent');

        if (!isset($orders) && !isset($menuItemId)) {
            return new JsonResponse([
                'success' => false,
                'fields' => [],
                'message' => 'No order or menu item selected.'
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
            $getMenuItem?->setWeight($orderItem['order']);
            $em->persist($getMenuItem);
            $em->flush();
        }

        return new JsonResponse([
            'success' => true,
            'fields' => [],
            'message' => 'You have successfully updated the menu item'
        ]);
    }

    #[Route('/dashboard/menu-item/{uuid}/delete', name: 'dashboard_menu_item_remove')]
    public function delete(Request $request, EntityManagerInterface $em, FileUploader $fileUploader): JsonResponse
    {
        $data = $request->request->all();
        $parentId = $data['menu_item']['parentId'];

        $menuItem = $em->getRepository(MenuItem::class)->find($parentId);
        if (!isset($menuItem)) return new JsonResponse(['success' => false, 'fields' => [], 'message' => 'This menu does not exist']);

        $icon = $menuItem->getImage();

        if (isset($icon)) {
            try {
                //Remove icon for the filesystem
                $fileUploader->removeFile("menuitem", $menuItem->getImage());
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
            'fields' => [],
            'message' => 'You have successfully removed the menu item.'
        ]);
    }

}
