<?php

namespace App\Controller\Dashboard;

use App\Entity\CookMenu;
use App\Entity\User;
use App\Form\Type\CookMenuFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class CookController extends AbstractController
{
    /**
     * @Route("/dashboard/cook/menus", name="dashboard_cook_menus")
     */
    public function menus(): Response
    {
        return $this->render('dashboard/cook/index.html.twig');
    }

    /**
     * @Route("/dashboard/cook/menu/add", name="dashboard_cook_menu_add")
     */
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $cookMenu = new CookMenu();
        $form = $this->createForm(CookMenuFormType::class, $cookMenu, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cookMenu->setUid(Uuid::v4());
            $cookMenu->setCreatedAt(new \DateTime());

            $em->persist($cookMenu);
            $em->flush();

            return $this->redirectToRoute('dashboard_cook_menus');
        }

        return $this->render('dashboard/cook/add.html.twig', [
            'cookMenu' => $cookMenu,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/cook/menu/{uuid}/edit", name="dashboard_cook_menu_edit")
     */
    public function edit(Request $request, EntityManagerInterface $em, $uuid): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $cookMenu = $em->getRepository(CookMenu::class)->findOneBy(['uid' => $uuid]);

        if (null === $cookMenu) {
            return $this->redirectToRoute('dashboard_cook_menus');
        }

        $form = $this->createForm(CookMenuFormType::class, $cookMenu, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cookMenu);
            $em->flush();

            return $this->redirectToRoute('dashboard_cook_menus');
        }

        return $this->render('dashboard/cook/edit.html.twig', [
            'cookMenu' => $cookMenu,
            'form' => $form->createView()
        ]);
    }
}
