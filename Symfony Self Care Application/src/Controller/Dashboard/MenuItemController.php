<?php

namespace App\Controller\Dashboard;

use App\Entity\Language;
use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Entity\MenuItemTranslation;
use App\Form\Type\MenuItemFormType;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuItemController extends AbstractController
{
    #[Route('/dashboard/menu/item/{uuid}/view', name: 'dashboard_menu_items_index')]
    public function index(EntityManagerInterface $em, Request $request, $uuid, TranslatorInterface $translator): Response
    {
        /**
         * Get menu by @uuid
         * @var Menu $menu
         */
        $menu = $em->getRepository(Menu::class)->findOneBy(['uuid' => $uuid]);

        if (null === $menu) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));

            return $this->redirectToRoute('dashboard_menu_index');
        }

        // init form & handle request data
        $form = $this->createForm(MenuItemFormType::class);
        $form->handleRequest($request);

        return $this->render('dashboard/menu/menu-item/actions.html.twig', [
            'uuid' => $uuid,
            'pageTitle' => $translator->trans('controller.link_management', [], 'messages') . $menu->getTitle(),
            'menuTitle' => $menu->getTitle(),
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/menu/item/{uuid}/create', name: 'dashboard_menu_item_create')]
    public function create(EntityManagerInterface $em, Request $request, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid, TranslatorInterface $translator): Response
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
                'message' => $translator->trans('controller.no_content', [], 'messages')
            ]);
        }

        // Init form & handle request data
        $menuItem = new MenuItem();
        $form = $this->createForm(MenuItemFormType::class, $menuItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get data from the file
            $file = $form->get('icon')->getData();

            /**
             * Get all available languages
             * @var Language $languages
             */
            $languages = $languageHelper->getAllLanguage();

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

            // Parse and save child @menuItem
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
            // Check and set errors
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }

            return new JsonResponse([
                'success' => false,
                'errors' => $errors,
                'message' => $translator->trans('controller.check_form_errors', [], 'messages')
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'errors' => [],
            'message' =>  $translator->trans('controller.success_item_added', [], 'messages')
        ]);
    }

    #[Route('/dashboard/menu/item/{uuid}/edit', name: 'dashboard_menu_item_edit')]
    public function edit(EntityManagerInterface $em, Request $request, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid, TranslatorInterface $translator): Response
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
                'message' => $translator->trans('controller.no_content', [], 'messages')
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
                'message' => $translator->trans('controller.no_content', [], 'messages')
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
                'message' => $translator->trans('controller.no_content', [], 'messages')
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
                'message' => $translator->trans('controller.check_form_errors', [], 'messages')
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'errors' => [],
            'message' => $translator->trans('controller.success_updated', [], 'messages')
        ]);
    }

    #[Route('/dashboard/menu/item/{uuid}/update-node', name: 'dashboard_menu_item_update_node')]
    public function updateNode(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): JsonResponse
    {
        $orders = $request->get('order');
        $menuItemId = $request->get('menu-item-id');
        $menuItemParent = $request->get('menu-item-parent');

        if (!isset($orders) && !isset($menuItemId)) {
            return new JsonResponse([
                'success' => false,
                'message' => $translator->trans('form.default.required', [], 'messages')
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
            'message' => $translator->trans('controller.success_updated', [], 'messages')
        ]);
    }

    #[Route('/dashboard/menu/item/{uuid}/delete', name: 'dashboard_menu_item_remove')]
    public function delete(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, TranslatorInterface $translator): JsonResponse
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
                'message' => $translator->trans('controller.no_content', [], 'messages')
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
            'message' => $translator->trans('controller.success_delete', [], 'messages')
        ]);
    }
}
