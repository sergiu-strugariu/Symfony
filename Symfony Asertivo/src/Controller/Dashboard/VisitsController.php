<?php

namespace App\Controller\Dashboard;

use App\Entity\PacientVisitCalendar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VisitsController extends AbstractController
{
    const TABS = [
        [
            'path' => 'dashboard_visits_calendar',
            'path_params' => [],
            'name' => 'Programari',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_RECEPTION']
        ],
        [
            'path' => 'dashboard_visits_list',
            'path_params' => [],
            'name' => 'Lista vizite',
            'items' => [],
            'roles' => ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_RECEPTION']
        ]
    ];

    /**
     * @Route("/dashboard/visits/calendar", name="dashboard_visits_calendar")
     */
    public function calendar(Request $request): Response
    {
        $date = $request->get('date', date('Y-m-d'));
        $selectedNursingHomeUuid = $request->get('location', '');
        $user = $this->getUser();
        $nursingHomes = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $nursingHomes = $user->getNursingHomes();
        } else {
            $selectedNursingHomeUuid = $user->getNursingHome()->getUid();
        }

        return $this->render('dashboard/visits/calendar.html.twig', [
            'tabs' => self::TABS,
            'date' => $date,
            'nursingHomes' => $nursingHomes,
            'selectedNursingHomeUuid' => $selectedNursingHomeUuid
        ]);
    }

    /**
     * @Route("/dashboard/visits/list", name="dashboard_visits_list")
     */
    public function visits(): Response
    {
        return $this->render('dashboard/visits/visits.html.twig', [
            'tabs' => self::TABS,
            'visitTypes' => PacientVisitCalendar::getVisitTypes()
        ]);
    }
}
