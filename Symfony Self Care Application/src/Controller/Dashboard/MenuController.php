<?php

namespace App\Controller\Dashboard;

use App\Entity\Menu;
use App\Form\Type\MenuFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuController extends AbstractController
{
    #[Route('/dashboard/menu', name: 'dashboard_menu_index')]
    public function index(): Response
    {
        return $this->render('dashboard/menu/index.html.twig');
    }

    #[Route('/dashboard/menu/create', name: 'dashboard_menu_create')]
    public function create(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuFormType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set @uuid
            $menu->setUuid(Uuid::v4());

            // Save data
            $em->persist($menu);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));
            return $this->redirectToRoute('dashboard_menu_index');
        }

        return $this->render('dashboard/menu/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.create_menu', [], 'messages')
        ]);
    }

    #[Route('/dashboard/menu/{uuid}/edit', name: 'dashboard_menu_edit')]
    public function edit(Request $request, EntityManagerInterface $em, $uuid, TranslatorInterface $translator): Response
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
        $form = $this->createForm(MenuFormType::class, $menu);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update data
            $em->persist($menu);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages'));
            return $this->redirectToRoute('dashboard_menu_index');
        }

        return $this->render('dashboard/menu/actions.html.twig', [
            'pageTitle' => $translator->trans('controller.edit_menu', [], 'messages'),
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/menu/{uuid}/delete', name: 'dashboard_menu_delete')]
    public function delete(EntityManagerInterface $em, $uuid, TranslatorInterface $translator): Response
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

        // Update data
        $menu->setDeletedAt(new DateTime());
        $em->persist($menu);
        $em->flush();

        // Set flash message
        $this->addFlash('success', $translator->trans('controller.success_delete', [], 'messages'));

        // Redirect to listing page
        return $this->redirectToRoute('dashboard_menu_index');
    }
}
