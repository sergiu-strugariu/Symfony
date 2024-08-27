<?php

namespace App\Controller\Dashboard;

use App\Entity\Menu;
use App\Form\Type\MenuType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class MenuController extends AbstractController
{
    #[Route('/dashboard/menus', name: 'dashboard_menu_index')]
    public function index(): Response
    {
        return $this->render('dashboard/menu/index.html.twig');
    }

    #[Route('/dashboard/menu/create', name: 'dashboard_menu_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set @uuid
            $menu->setUuid(Uuid::v4());

            // Save data
            $em->persist($menu);
            $em->flush();

            // Set flash message
            $this->addFlash('success', 'Congratulations, you have successfully added a new menu.');
            return $this->redirectToRoute('dashboard_menu_index');
        }

        return $this->render('dashboard/menu/management.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/dashboard/menu/{uuid}/edit', name: 'dashboard_menu_edit')]
    public function edit(Request $request, EntityManagerInterface $em, $uuid): Response
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
        $form = $this->createForm(MenuType::class, $menu);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update data
            $em->persist($menu);
            $em->flush();

            // Set flash message
            $this->addFlash('success', 'Congratulations, you have successfully edited menu.');
            return $this->redirectToRoute('dashboard_menu_index');
        }

        return $this->render('dashboard/menu/management.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/menu/{uuid}/delete', name: 'dashboard_menu_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
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

        // Update data
        $menu->setDeletedAt(new DateTime());
        $em->persist($menu);
        $em->flush();

        // Set flash message
        $this->addFlash('success', 'This menu has been successfully deleted.');

        // Redirect to listing page
        return $this->redirectToRoute('dashboard_menu_index');
    }
}
