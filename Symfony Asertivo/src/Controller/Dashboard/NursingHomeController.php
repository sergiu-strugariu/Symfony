<?php

namespace App\Controller\Dashboard;

use App\Entity\NursingHome;
use App\Entity\NursingHomeRoom;
use App\Form\Type\NursingHomeFormType;
use App\Form\Type\NursingHomeRoomFormType;
use App\Repository\NursingHomeRepository;
use App\Repository\NursingHomeRoomRepository;
use App\Repository\PacientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NursingHomeController extends AbstractController
{
    const TABS = [
        [
            'path' => 'dashboard_nursing_homes',
            'path_params' => [],
            'name' => 'Cămine',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ]
    ];
    const NURSING_HOME_TABS = [
        [
            'path' => 'dashboard_nursing_home_edit',
            'name' => 'Date cămin',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ],
        [
            'path' => 'dashboard_nursing_home_rooms',
            'path_params' => [],
            'name' => 'Camere',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT']
        ]
    ];

    /**
     * @Route("/dashboard/nursing-homes", name="dashboard_nursing_homes")
     */
    public function index(): Response
    {
        $form = $this->createForm(NursingHomeFormType::class);

        return $this->render('dashboard/nursing_home/index.html.twig', [
            'tabs' => self::TABS,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/nursing-homes/{uuid}/edit", name="dashboard_nursing_home_edit")
     */
    public function edit(Request $request, EntityManagerInterface $em, NursingHomeRepository $nursingHomeRepository, $uuid): Response
    {
        /** @var NursingHome $nursingHome */
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $uuid]);

        if (null === $nursingHome) {
            $this->addFlash('danger', "Oops! Se pare că acest cămin nu există.");
            return $this->redirectToRoute('dashboard_nursing_homes');
        }

        $form = $this->createForm(NursingHomeFormType::class, $nursingHome);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($nursingHome);
            $em->flush();

            // Set flash message
            $this->addFlash('success', "Felicitări! Datele au fost actualizate cu success!");
            return $this->redirectToRoute('dashboard_nursing_homes');
        }

        return $this->render('dashboard/nursing_home/edit.twig', [
            'form' => $form->createView(),
            'nursingHome' => $nursingHome,
            'tabs' => self::NURSING_HOME_TABS
        ]);
    }

    /**
     * @Route("/dashboard/nursing-homes/{uuid}/rooms", name="dashboard_nursing_home_rooms")
     */
    public function rooms(NursingHomeRepository $nursingHomeRepository, NursingHomeRoomRepository $nursingHomeRoomRepository, PacientRepository $pacientRepository, $uuid): Response
    {
        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $uuid]);

        if (null === $nursingHome) {
            return $this->redirectToRoute('dashboard_nursing_homes');
        }

        $nursingHomeRoom = new NursingHomeRoom();
        $form = $this->createForm(NursingHomeRoomFormType::class, $nursingHomeRoom);

        $nursingHomeRooms = $nursingHomeRoomRepository->findRoomsList($nursingHome);
        $rooms = [];
        foreach ($nursingHomeRooms as $nursingHomeRoom) {
            $pacientsByRoom = $pacientRepository->findPacientsByRoom($nursingHomeRoom);
            $pacientsCountByRoom = count($pacientsByRoom);
            $totalBeds = $nursingHomeRoom->getNumberOfBeds();
            $availableBeds = $totalBeds - $pacientsCountByRoom;

            $rooms[] = [
                'uuid' => $nursingHomeRoom->getUid(),
                'nursingHome' => $nursingHomeRoom->getNursingHome()->getName(),
                'number' => $nursingHomeRoom->getRoomNumber(),
                'floor' => $nursingHomeRoom->getFloor(),
                'totalBeds' => $totalBeds,
                'details' => $nursingHomeRoom->getDetails(),
                'type' => $nursingHomeRoom->getRoomType(),
                'availableBeds' => $availableBeds < 0 ? 0 : $availableBeds,
                'pacients' => implode(', ', array_column($pacientsByRoom, 'name'))
            ];
        }

        return $this->render('dashboard/nursing_home/rooms.html.twig', [
            'tabs' => self::NURSING_HOME_TABS,
            'nursingHome' => $nursingHome,
            'form' => $form->createView(),
            'rooms' => $rooms
        ]);
    }
}
