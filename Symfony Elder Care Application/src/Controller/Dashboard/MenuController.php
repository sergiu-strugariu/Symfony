<?php

namespace App\Controller\Dashboard;

use App\Entity\Menu;
use App\Form\MenuFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use DateTime;

class MenuController extends AbstractController
{
    /**
     * @Route("/dashboard/secure/menu", name="dashboard_menu_index")
     */
    public function index(): Response
    {
        return $this->render('dashboard/menu/index.html.twig');
    }

    /**
     * @Route("/dashboard/secure/menu/create", name="dashboard_menu_create")
     */
    public function create(Request $request, EntityManagerInterface $em): Response
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
            $this->addFlash('success', 'Felicitări, ați adăugat cu succes un element nou');
            return $this->redirectToRoute('dashboard_menu_index');
        }

        return $this->render('dashboard/menu/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => 'Creați meniu'
        ]);
    }

    /**
     * @Route("/dashboard/secure/menu/{uuid}/edit", name="dashboard_menu_edit")
     */
    public function edit(Request $request, EntityManagerInterface $em, $uuid): Response
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
        $form = $this->createForm(MenuFormType::class, $menu);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update data
            $em->persist($menu);
            $em->flush();

            // Set flash message
            $this->addFlash('success', 'Ați editat cu succes acest conținut');
            return $this->redirectToRoute('dashboard_menu_index');
        }

        return $this->render('dashboard/menu/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => 'Meniu Editare'
        ]);
    }

    /**
     * @Route("/dashboard/secure/menu/{uuid}/delete", name="dashboard_menu_delete")
     */
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        /**
         * Get menu by @uuid
         * @var Menu $menu
         */
        $menu = $em->getRepository(Menu::class)->findOneBy(['uuid' => $uuid]);

        if (null === $menu) {
            // Set flash message
            $this->addFlash('danger', 'Acest conținut nu există.');

            return $this->redirectToRoute('dashboard_menu_index');
        }

        // Update data
        $menu->setDeletedAt(new DateTime());
        $em->persist($menu);
        $em->flush();

        // Set flash message
        $this->addFlash('success', 'Acest conținut a fost șters cu succes.');

        // Redirect to listing page
        return $this->redirectToRoute('dashboard_menu_index');
    }
}
