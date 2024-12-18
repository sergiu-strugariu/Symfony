<?php

namespace App\Controller\Dashboard;

use App\Entity\County;
use App\Entity\User;
use App\Entity\UserBillingData;
use App\Form\Type\UserPersonalDataFormType;
use App\Helper\MenuHelper;
use App\Repository\NursingHomeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/dashboard/users", name="dashboard_users")
     */
    public function index(Request $request, NursingHomeRepository $nursingHomeRepository, MenuHelper $menuHelper): Response
    {
        // get all roles from the DB
        $roles = $menuHelper->getUserRoles();
        $role = $request->get('role', 'ROLE_ADMIN');

        /** @var User $user */
        $user = $this->getUser();

        $nursingHomes = $nursingHomeRepository->findNursingHomesByUser($user);

        return $this->render('dashboard/user/index.html.twig', [
            'roles' => $roles,
            'role' => $role,
            'nursingHomes' => $nursingHomes
        ]);
    }

    /**
     * @Route("/dashboard/profile", name="dashboard_user_profile")
     */
    public function profile(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // check if user exists
        if (null === $user) {
            return $this->redirectToRoute('dashboard');
        }

        $form = $this->createForm(UserPersonalDataFormType::class, $user);

        $companies = $em->getRepository(UserBillingData::class)->findBy(
            ['user' => $user],
            ['isFavorite' => 'DESC']
        );

        $counties = $em->getRepository(County::class)->findAll();

        return $this->render('dashboard/user/profile.html.twig', [
            'counties' => $counties,
            'companies' => $companies,
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/profile/{uuid}/view", name="dashboard_user_view")
     */
    public function view(UserRepository $userRepository, $uuid): Response
    {
        // find user by UUID
        $user = $userRepository->findOneBy(['uid' => $uuid]);

        // check if user exists
        if (null === $user) {
            return $this->redirectToRoute('dashboard_users');
        }

        $form = $this->createForm(UserPersonalDataFormType::class, $user);

        return $this->render('dashboard/user/view.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}
